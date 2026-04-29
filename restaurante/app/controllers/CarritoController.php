<?php
// Archivo: app/controllers/CarritoController.php

require_once BASE_PATH . 'app/Models/CarritoModel.php';
require_once BASE_PATH . 'app/Models/CompraModel.php';
require_once BASE_PATH . 'app/Models/MenuModel.php';
require_once BASE_PATH . 'app/Helpers/Auth.php';

class CarritoController {
    private CarritoModel $carrito;
    private CompraModel  $compra;

    public function __construct() {
        $this->carrito = new CarritoModel();
        $this->compra  = new CompraModel();
    }

    private function requireValidCsrf(): void {
        $session = $_SESSION['csrf_token'] ?? '';
        $request = $_POST['csrf_token'] ?? '';
        if ($session === '' || !hash_equals($session, $request)) {
            http_response_code(419);
            exit('Token CSRF inválido.');
        }
    }

    /** GET /?controller=Carrito&action=index — ver carrito */
    public function index(): void {
        Auth::requireLogin();
        $userId = Auth::userId();
        $items  = $this->carrito->getByUser($userId);
        $total  = array_reduce($items, fn($s, $i) => $s + ($i['precio'] * $i['cantidad']), 0);
        require_once BASE_PATH . 'app/views/carrito/index.php';
    }

    /** POST /?controller=Carrito&action=agregar */
    public function agregar(): void {
        Auth::requireLogin();
        $this->requireValidCsrf();
        $platoId = (int) ($_POST['plato_id'] ?? 0);
        if ($platoId > 0) {
            $this->carrito->agregar(Auth::userId(), $platoId);
        }
        $this->redirigirConFeedback('agregado');
    }

    /** POST /?controller=Carrito&action=restar */
    public function restar(): void {
        Auth::requireLogin();
        $this->requireValidCsrf();
        $platoId = (int) ($_POST['plato_id'] ?? 0);
        if ($platoId > 0) {
            $this->carrito->restar(Auth::userId(), $platoId);
        }
        header('Location: /?controller=Carrito&action=index');
        exit;
    }

    /** POST /?controller=Carrito&action=eliminar */
    public function eliminar(): void {
        Auth::requireLogin();
        $this->requireValidCsrf();
        $platoId = (int) ($_POST['plato_id'] ?? 0);
        if ($platoId > 0) {
            $this->carrito->eliminar(Auth::userId(), $platoId);
        }
        header('Location: /?controller=Carrito&action=index');
        exit;
    }

    /** POST /?controller=Carrito&action=pagar */
    public function pagar(): void {
        Auth::requireLogin();
        $this->requireValidCsrf();

        $userId = Auth::userId();
        $items  = $this->carrito->getByUser($userId);

        if (empty($items)) {
            header('Location: /?controller=Carrito&action=index&error=vacio');
            exit;
        }

        // Crear la compra en BD
        $compraId = $this->compra->crear($userId, $items);

        // Vaciar el carrito
        $this->carrito->vaciar($userId);

        // Redirigir al comprobante
        header('Location: /?controller=Carrito&action=comprobante&id=' . $compraId);
        exit;
    }

    /** GET /?controller=Carrito&action=comprobante&id=X — confirmación post-pago */
    public function comprobante(): void {
        Auth::requireLogin();
        $compraId = (int) ($_GET['id'] ?? 0);

        // Traer detalle de la compra
        $db   = (new \Database())->getConnection();
        $stmt = $db->prepare(
            "SELECT cd.cantidad, cd.precio_unitario, p.nombre, c.total, c.created_at
             FROM compra_detalles cd
             JOIN platos p ON p.id = cd.plato_id
             JOIN compras c ON c.id = cd.compra_id
             WHERE cd.compra_id = :cid AND c.user_id = :uid"
        );
        $stmt->execute([':cid' => $compraId, ':uid' => Auth::userId()]);
        $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($detalles)) {
            header('Location: /');
            exit;
        }

        $total     = $detalles[0]['total'];
        $createdAt = $detalles[0]['created_at'];
        require_once BASE_PATH . 'app/views/carrito/comprobante.php';
    }

    private function redirigirConFeedback(string $key): void {
        $ref = $_SERVER['HTTP_REFERER'] ?? '/';
        // Evitar open redirect: solo redirigir a rutas internas
        if (!str_starts_with($ref, '/') && !str_contains(parse_url($ref, PHP_URL_HOST) ?? '', $_SERVER['HTTP_HOST'] ?? '')) {
            $ref = '/';
        }
        $sep = str_contains($ref, '?') ? '&' : '?';
        header('Location: ' . $ref . $sep . 'carrito_ok=' . $key);
        exit;
    }
}
