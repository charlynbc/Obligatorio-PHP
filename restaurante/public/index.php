<?php
// Archivo: public/index.php — Front Controller MVC

// Pasar archivos estáticos directamente cuando se usa php -S
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . $_SERVER['REQUEST_URI'])) {
    return false;
}

define('BASE_PATH', __DIR__ . '/../');

// ── Autoloader de Composer (necesario para symfony/mailer y otras librerías) ──
require_once BASE_PATH . 'vendor/autoload.php';

// ── Sesión segura ──────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,   // cambiar a true en producción con HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Generar token CSRF si no existe en sesión
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── Helpers globales de autenticación ─────────────────────────────────────────
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function csrfToken(): string {
    return $_SESSION['csrf_token'] ?? '';
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('Solicitud inválida.');
    }
}

require_once BASE_PATH . 'app/config/Database.php';

// ── Router seguro: whitelist de controladores ──────────────────────────────────
// Previene Path Traversal: solo se permiten controladores conocidos
$allowed = ['Menu', 'Usuario', 'Carrito', 'Favoritos'];
$c = $_GET['controller'] ?? 'Menu';
if (!in_array($c, $allowed, true)) {
    http_response_code(404);
    exit('Página no encontrada.');
}
$controllerName = $c . 'Controller';

// Solo letras y números en el nombre de la acción (previene inyección de rutas)
$actionName = $_GET['action'] ?? 'index';
if (!preg_match('/^[a-zA-Z][a-zA-Z0-9]{0,49}$/', $actionName)) {
    http_response_code(404);
    exit('Página no encontrada.');
}

$controllerPath = BASE_PATH . 'app/controllers/' . $controllerName . '.php';

if (!file_exists($controllerPath)) {
    http_response_code(404);
    exit('Página no encontrada.');
}

require_once $controllerPath;
$controller = new $controllerName();

if (!method_exists($controller, $actionName)) {
    http_response_code(404);
    exit('Página no encontrada.');
}

$controller->$actionName();
