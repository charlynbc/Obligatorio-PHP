<?php

namespace Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class MvcSmokeTest extends TestCase
{
    private const CLIENT_EMAIL = 'demo.cliente@mangiare.local';
    private const CLIENT_PASSWORD = 'MangiareCliente2026!';
    private const ADMIN_EMAIL = 'demo.admin@mangiare.local';
    private const ADMIN_PASSWORD = 'MangiareAdmin2026!';

    private const CATEGORY_OPTIONS = [
        'Entradas',
        'Ensaladas',
        'Pastas',
        'Pizzas',
        'Principales',
        'Postres',
        'Bebidas sin alcohol',
        'Bebidas con alcohol',
    ];

    private const VISIBLE_MENU_ORDER = [
        'Entradas',
        'Ensaladas',
        'Pastas',
        'Pizzas',
        'Principales',
        'Postres',
        'Bebidas sin alcohol',
    ];

    private static string $basePath;
    private static string $dbPath;
    private static string $dbBackupPath;
    private static string $baseUrl;
    private static array $baselineUploadedFiles = [];
    private static ?Process $serverProcess = null;

    private Client $http;
    private CookieJar $cookies;
    private array $temporaryFiles = [];

    public static function setUpBeforeClass(): void
    {
        self::$basePath = dirname(__DIR__, 2);
        self::$dbPath = self::$basePath . '/database/database.sqlite';
        self::$dbBackupPath = tempnam(sys_get_temp_dir(), 'mvc-db-');
        self::$baselineUploadedFiles = self::listUploadedFiles();

        if (self::$dbBackupPath === false) {
            throw new RuntimeException('No se pudo crear el archivo temporal para backup de SQLite.');
        }

        if (!copy(self::$dbPath, self::$dbBackupPath)) {
            throw new RuntimeException('No se pudo crear el backup inicial de la base SQLite.');
        }

        self::startServer();
    }

    public static function tearDownAfterClass(): void
    {
        self::restoreDatabase();
        self::cleanupGeneratedUploadFiles();

        if (self::$serverProcess instanceof Process && self::$serverProcess->isRunning()) {
            self::$serverProcess->stop(1);
        }

        if (isset(self::$dbBackupPath) && is_file(self::$dbBackupPath)) {
            @unlink(self::$dbBackupPath);
        }
    }

    protected function setUp(): void
    {
        self::restoreDatabase();
        self::cleanupGeneratedUploadFiles();

        $this->initializeHttpClient();
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $filePath) {
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        $this->temporaryFiles = [];
        parent::tearDown();
    }

    public function test_guest_home_shows_menu_sorted_by_style(): void
    {
        $html = $this->getHtml('/?controller=Menu&action=index');

        self::assertStringContainsString('Orden del menú', $html);
        self::assertSame(self::VISIBLE_MENU_ORDER, $this->extractCategoryBadges($html));
        self::assertSame(count(self::VISIBLE_MENU_ORDER), substr_count($html, 'class="badge menu-category mb-2 align-self-start"'));
    }

    public function test_client_can_complete_full_purchase_flow(): void
    {
        $clientId = (int) $this->dbScalar("SELECT id FROM users WHERE email = '" . self::CLIENT_EMAIL . "'");
        $homeHtml = $this->login(self::CLIENT_EMAIL, self::CLIENT_PASSWORD);

        self::assertStringContainsString('Mi Perfil', $homeHtml);
        self::assertStringContainsString('Agregar al carrito', $homeHtml);

        $platoId = $this->extractFirstPlatoId($homeHtml);
        $favoritesBefore = (int) $this->dbScalar("SELECT COUNT(*) FROM favoritos WHERE user_id = {$clientId} AND plato_id = {$platoId}");

        $homeHtml = $this->postHtml('/?controller=Favoritos&action=toggle', [
            'plato_id' => $platoId,
            'csrf_token' => $this->extractCsrf($homeHtml),
        ]);
        $homeHtml = $this->postHtml('/?controller=Favoritos&action=toggle', [
            'plato_id' => $platoId,
            'csrf_token' => $this->extractCsrf($homeHtml),
        ]);

        $favoritesAfter = (int) $this->dbScalar("SELECT COUNT(*) FROM favoritos WHERE user_id = {$clientId} AND plato_id = {$platoId}");
        self::assertSame($favoritesBefore, $favoritesAfter);

        $cartHtml = $this->postHtml('/?controller=Carrito&action=agregar', [
            'plato_id' => $platoId,
            'csrf_token' => $this->extractCsrf($homeHtml),
        ]);
        self::assertStringContainsString('Mi Carrito', $cartHtml);

        $cartHtml = $this->postHtml('/?controller=Carrito&action=agregar', [
            'plato_id' => $platoId,
            'csrf_token' => $this->extractCsrf($cartHtml),
        ]);
        self::assertSame('2', $this->dbScalar("SELECT cantidad FROM carrito_items WHERE user_id = {$clientId} AND plato_id = {$platoId}"));

        $cartHtml = $this->postHtml('/?controller=Carrito&action=restar', [
            'plato_id' => $platoId,
            'csrf_token' => $this->extractCsrf($cartHtml),
        ]);
        self::assertSame('1', $this->dbScalar("SELECT cantidad FROM carrito_items WHERE user_id = {$clientId} AND plato_id = {$platoId}"));

        $confirmHtml = $this->postHtml('/?controller=Carrito&action=pagar', [
            'csrf_token' => $this->extractCsrf($cartHtml),
        ]);
        self::assertStringContainsString('Ver comprobante', $confirmHtml);

        $compraId = $this->extractCompraId($confirmHtml);
        $receiptHtml = $this->getHtml('/?controller=Carrito&action=comprobante&id=' . $compraId);
        self::assertStringContainsString('Comprobante disponible', $receiptHtml);

        $profileHtml = $this->getHtml('/?controller=Usuario&action=perfil');
        self::assertStringContainsString('Compra #' . $compraId, $profileHtml);
        self::assertSame('0', $this->dbScalar("SELECT COUNT(*) FROM carrito_items WHERE user_id = {$clientId}"));
    }

    public function test_client_can_update_profile_name_and_password(): void
    {
        $clientId = (int) $this->dbScalar("SELECT id FROM users WHERE email = '" . self::CLIENT_EMAIL . "'");
        $profileHtml = $this->login(self::CLIENT_EMAIL, self::CLIENT_PASSWORD);
        $profileHtml = $this->getHtml('/?controller=Usuario&action=perfil');

        $newName = 'Cliente QA Actualizado';
        $profileHtml = $this->postHtml('/?controller=Usuario&action=perfil', [
            'name' => $newName,
            'current_password' => '',
            'new_password' => '',
            'confirm_password' => '',
            'csrf_token' => $this->extractCsrf($profileHtml),
        ]);
        self::assertStringContainsString('Datos actualizados correctamente.', $profileHtml);
        self::assertStringContainsString($newName, $profileHtml);
        self::assertSame($newName, $this->dbScalar("SELECT name FROM users WHERE id = {$clientId}"));

        $invalidPasswordHtml = $this->postHtml('/?controller=Usuario&action=perfil', [
            'name' => $newName,
            'current_password' => 'incorrecta',
            'new_password' => 'NuevaClaveQA123!',
            'confirm_password' => 'NuevaClaveQA123!',
            'csrf_token' => $this->extractCsrf($profileHtml),
        ]);
        self::assertStringContainsString('La contraseña actual es incorrecta.', $invalidPasswordHtml);

        $updatedPasswordHtml = $this->postHtml('/?controller=Usuario&action=perfil', [
            'name' => $newName,
            'current_password' => self::CLIENT_PASSWORD,
            'new_password' => 'NuevaClaveQA123!',
            'confirm_password' => 'NuevaClaveQA123!',
            'csrf_token' => $this->extractCsrf($invalidPasswordHtml),
        ]);
        self::assertStringContainsString('Datos actualizados correctamente.', $updatedPasswordHtml);

        $passwordHash = $this->dbScalar("SELECT password FROM users WHERE id = {$clientId}");
        self::assertTrue(password_verify('NuevaClaveQA123!', $passwordHash));

        $this->postHtml('/?controller=Usuario&action=logout', [
            'csrf_token' => $this->extractCsrf($updatedPasswordHtml),
        ]);

        $this->initializeHttpClient();
        $homeHtml = $this->login(self::CLIENT_EMAIL, 'NuevaClaveQA123!');
        self::assertStringContainsString($newName, $homeHtml);
    }

    public function test_admin_can_upload_and_replace_menu_image(): void
    {
        $homeHtml = $this->login(self::ADMIN_EMAIL, self::ADMIN_PASSWORD);
        $platoId = (int) $this->dbScalar("SELECT id FROM platos WHERE imagen_url NOT LIKE '/img/%' OR imagen_url IS NULL ORDER BY id ASC LIMIT 1");
        self::assertGreaterThan(0, $platoId);

        $firstUploadHtml = $this->uploadMenuImage($platoId, $this->extractCsrf($homeHtml), $this->createTempPngFile());
        $firstImage = $this->dbScalar("SELECT imagen_url FROM platos WHERE id = {$platoId}");
        self::assertMatchesRegularExpression('#^/img/plato_' . $platoId . '_\\d+_[a-f0-9]{8}\\.png$#', $firstImage);
        self::assertFileExists(self::$basePath . '/public' . $firstImage);

        $secondUploadHtml = $this->uploadMenuImage($platoId, $this->extractCsrf($firstUploadHtml), $this->createTempPngFile());
        $secondImage = $this->dbScalar("SELECT imagen_url FROM platos WHERE id = {$platoId}");

        self::assertNotSame($firstImage, $secondImage);
        self::assertFileDoesNotExist(self::$basePath . '/public' . $firstImage);
        self::assertFileExists(self::$basePath . '/public' . $secondImage);
        self::assertStringContainsString('Cambiar imagen', $secondUploadHtml);
    }

    public function test_admin_can_manage_menu_and_category_validation_is_enforced(): void
    {
        $homeHtml = $this->login(self::ADMIN_EMAIL, self::ADMIN_PASSWORD);

        self::assertStringContainsString('Panel de administración', $homeHtml);
        self::assertStringContainsString('Venta en local', $homeHtml);
        self::assertSame(self::CATEGORY_OPTIONS, $this->extractSelectOptions($homeHtml, 'categoria'));

        $invalidHtml = $this->postHtml('/?controller=Menu&action=create', [
            'nombre' => 'Prueba inválida',
            'categoria' => 'Categoria inventada',
            'precio' => '123',
            'descripcion' => 'No deberia guardarse',
            'csrf_token' => $this->extractCsrf($homeHtml),
        ]);
        self::assertStringContainsString('Seleccioná una categoría válida de la lista.', $invalidHtml);

        $adminPlatoId = $this->extractFirstLocalSalePlatoId($homeHtml);
        $localSaleHtml = $this->postHtml('/?controller=Menu&action=localSale', [
            'cantidad_' . $adminPlatoId => '1',
            'csrf_token' => $this->extractCsrf($homeHtml),
        ]);
        self::assertStringContainsString('Venta en local registrada correctamente.', $localSaleHtml);

        $createHtml = $this->postHtml('/?controller=Menu&action=create', [
            'nombre' => 'Especial del chef QA',
            'categoria' => 'Bebidas con alcohol',
            'precio' => '777',
            'descripcion' => 'Plato temporal para smoke test.',
            'csrf_token' => $this->extractCsrf($localSaleHtml),
        ]);
        self::assertStringContainsString('Plato creado correctamente.', $createHtml);

        $platoId = (int) $this->dbScalar("SELECT id FROM platos WHERE nombre = 'Especial del chef QA' ORDER BY id DESC LIMIT 1");
        self::assertGreaterThan(0, $platoId);

        $updateHtml = $this->postHtml('/?controller=Menu&action=update', [
            'id' => (string) $platoId,
            'nombre' => 'Especial del chef QA editado',
            'categoria' => 'Bebidas con alcohol',
            'precio' => '799',
            'descripcion' => 'Plato temporal editado para smoke test.',
            'csrf_token' => $this->extractCsrf($createHtml),
        ]);
        self::assertStringContainsString('Plato actualizado correctamente.', $updateHtml);

        $deleteResponse = $this->postResponse('/?controller=Menu&action=delete', [
            'id' => (string) $platoId,
            'csrf_token' => $this->extractCsrf($updateHtml),
        ]);
        self::assertSame(200, $deleteResponse->getStatusCode());
        self::assertStringContainsString('Plato eliminado correctamente.', (string) $deleteResponse->getBody());

        $cartResponse = $this->http->get('/?controller=Carrito&action=index');
        self::assertSame(403, $cartResponse->getStatusCode());
        self::assertStringContainsString('Acceso denegado', (string) $cartResponse->getBody());
    }

    private static function startServer(): void
    {
        $port = self::findFreePort();
        self::$baseUrl = 'http://127.0.0.1:' . $port;

        self::$serverProcess = new Process(
            [PHP_BINARY, '-S', '127.0.0.1:' . $port, '-t', 'public', 'public/index.php'],
            self::$basePath,
            ['XDEBUG_MODE' => 'off']
        );
        self::$serverProcess->start();

        $healthClient = new Client([
            'base_uri' => self::$baseUrl,
            'http_errors' => false,
            'timeout' => 1,
        ]);

        $deadline = microtime(true) + 10;
        while (microtime(true) < $deadline) {
            usleep(200000);

            try {
                $response = $healthClient->get('/?controller=Menu&action=index');
                if ($response->getStatusCode() === 200) {
                    return;
                }
            } catch (Throwable) {
                // seguir intentando hasta timeout
            }
        }

        $output = '';
        if (self::$serverProcess instanceof Process) {
            $output = self::$serverProcess->getOutput() . self::$serverProcess->getErrorOutput();
            self::$serverProcess->stop(1);
        }

        throw new RuntimeException('No se pudo iniciar el servidor MVC para tests. ' . trim($output));
    }

    private static function findFreePort(): int
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
        if ($socket === false) {
            throw new RuntimeException('No se pudo obtener un puerto libre: ' . $errorMessage);
        }

        $address = stream_socket_get_name($socket, false);
        fclose($socket);

        if ($address === false || !str_contains($address, ':')) {
            throw new RuntimeException('No se pudo resolver el puerto libre para tests.');
        }

        return (int) substr(strrchr($address, ':'), 1);
    }

    private static function restoreDatabase(): void
    {
        if (!isset(self::$dbBackupPath) || !is_file(self::$dbBackupPath)) {
            return;
        }

        if (!copy(self::$dbBackupPath, self::$dbPath)) {
            throw new RuntimeException('No se pudo restaurar la base SQLite para tests.');
        }

        clearstatcache(true, self::$dbPath);
    }

    private static function listUploadedFiles(): array
    {
        $uploadDir = self::$basePath . '/public/img';
        if (!is_dir($uploadDir)) {
            return [];
        }

        return array_values(array_diff(scandir($uploadDir) ?: [], ['.', '..']));
    }

    private static function cleanupGeneratedUploadFiles(): void
    {
        $uploadDir = self::$basePath . '/public/img';
        if (!is_dir($uploadDir)) {
            return;
        }

        foreach (self::listUploadedFiles() as $fileName) {
            if (!in_array($fileName, self::$baselineUploadedFiles, true)) {
                @unlink($uploadDir . '/' . $fileName);
            }
        }
    }

    private function initializeHttpClient(): void
    {
        $this->cookies = new CookieJar();
        $this->http = new Client([
            'base_uri' => self::$baseUrl,
            'cookies' => $this->cookies,
            'http_errors' => false,
            'allow_redirects' => true,
            'timeout' => 10,
        ]);
    }

    private function login(string $email, string $password): string
    {
        $loginHtml = $this->getHtml('/?controller=Usuario&action=login');

        return $this->postHtml('/?controller=Usuario&action=login', [
            'email' => $email,
            'password' => $password,
            'csrf_token' => $this->extractCsrf($loginHtml),
        ]);
    }

    private function getHtml(string $path): string
    {
        $response = $this->http->get($path);
        self::assertSame(200, $response->getStatusCode(), 'GET falló para ' . $path);

        return (string) $response->getBody();
    }

    private function postHtml(string $path, array $formParams): string
    {
        $response = $this->postResponse($path, $formParams);
        self::assertSame(200, $response->getStatusCode(), 'POST falló para ' . $path);

        return (string) $response->getBody();
    }

    private function postResponse(string $path, array $formParams)
    {
        return $this->http->post($path, [
            'form_params' => $formParams,
        ]);
    }

    private function uploadMenuImage(int $platoId, string $csrfToken, string $filePath): string
    {
        $fileHandle = fopen($filePath, 'rb');
        if ($fileHandle === false) {
            self::fail('No se pudo abrir el archivo temporal para upload.');
        }

        try {
            $response = $this->http->post('/?controller=Menu&action=upload', [
                'multipart' => [
                    ['name' => 'csrf_token', 'contents' => $csrfToken],
                    ['name' => 'id', 'contents' => (string) $platoId],
                    [
                        'name' => 'imagen',
                        'contents' => $fileHandle,
                        'filename' => 'qa-image.png',
                        'headers' => ['Content-Type' => 'image/png'],
                    ],
                ],
            ]);
        } finally {
            if (is_resource($fileHandle)) {
                fclose($fileHandle);
            }
        }

        self::assertSame(200, $response->getStatusCode(), 'Upload falló para el plato ' . $platoId);

        return (string) $response->getBody();
    }

    private function createTempPngFile(): string
    {
        $filePath = tempnam(sys_get_temp_dir(), 'mvc-img-');
        if ($filePath === false) {
            self::fail('No se pudo crear un archivo temporal para la imagen de prueba.');
        }

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO5W9OQAAAAASUVORK5CYII=', true);
        if ($png === false) {
            self::fail('No se pudo generar la imagen PNG de prueba.');
        }

        file_put_contents($filePath, $png);
        $this->temporaryFiles[] = $filePath;

        return $filePath;
    }

    private function extractCsrf(string $html): string
    {
        if (!preg_match('/name=["\']csrf_token["\'] value=["\']([^"\']+)/', $html, $matches)) {
            self::fail('No se pudo extraer el token CSRF de la respuesta HTML.');
        }

        return $matches[1];
    }

    private function extractFirstPlatoId(string $html): int
    {
        if (!preg_match('/name=["\']plato_id["\'] value=["\'](\d+)/', $html, $matches)) {
            self::fail('No se pudo extraer un plato_id desde el HTML.');
        }

        return (int) $matches[1];
    }

    private function extractCompraId(string $html): int
    {
        if (!preg_match('/controller=Carrito&amp;action=comprobante&amp;id=(\d+)/', $html, $matches)
            && !preg_match('/controller=Carrito&action=comprobante&id=(\d+)/', $html, $matches)) {
            self::fail('No se pudo extraer el id del comprobante desde la confirmación.');
        }

        return (int) $matches[1];
    }

    private function extractCategoryBadges(string $html): array
    {
        preg_match_all('/class="badge menu-category mb-2 align-self-start">([^<]+)/', $html, $matches);

        return array_values(array_filter(array_map('trim', $matches[1] ?? [])));
    }

    private function extractSelectOptions(string $html, string $fieldName): array
    {
        $pattern = '/name="' . preg_quote($fieldName, '/') . '"[^>]*>(.*?)<\/select>/s';
        if (!preg_match($pattern, $html, $matches)) {
            self::fail('No se encontró el select para ' . $fieldName . '.');
        }

        preg_match_all('/<option value="([^"]*)"/', $matches[1], $options);

        return array_values(array_filter($options[1] ?? [], static fn (string $value): bool => $value !== ''));
    }

    private function extractFirstLocalSalePlatoId(string $html): int
    {
        if (!preg_match('/name="cantidad_(\d+)"/', $html, $matches)) {
            self::fail('No se pudo extraer un plato para venta local.');
        }

        return (int) $matches[1];
    }

    private function dbScalar(string $sql): string
    {
        $pdo = new PDO('sqlite:' . self::$dbPath);
        $value = $pdo->query($sql)?->fetchColumn();

        if ($value === false) {
            self::fail('La consulta SQLite no devolvió resultados: ' . $sql);
        }

        return (string) $value;
    }
}
