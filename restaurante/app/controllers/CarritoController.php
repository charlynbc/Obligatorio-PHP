<?php
// Archivo: app/controllers/CarritoController.php

require_once BASE_PATH . 'app/models/CarritoModel.php';
require_once BASE_PATH . 'app/models/ComprasModel.php';
require_once BASE_PATH . 'app/models/MailService.php';
require_once BASE_PATH . 'app/config/Mail.php';

class CarritoController {

    // Solo clientes autenticados pueden acceder al carrito
    private function requireCliente(): void {
        if (!isLoggedIn()) {
            header('Location: /?controller=Usuario&action=login');
            exit;
        }

        if (isAdmin()) {
            http_response_code(403);
            exit('Acceso denegado. El administrador del sistema no puede comprar ni usar el carrito.');
        }
    }

    // GET: ver el carrito
    public function index() {
        $this->requireCliente();
        $model  = new CarritoModel();
        $items  = $model->getByUser((int) $_SESSION['user_id']);
        $total  = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $items));
        require_once BASE_PATH . 'app/views/carrito.php';
    }

    // POST: agregar un plato al carrito
    public function agregar() {
        $this->requireCliente();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }
        verifyCsrf();

        $platoId = isset($_POST['plato_id']) ? (int) $_POST['plato_id'] : 0;
        if ($platoId > 0) {
            $model = new CarritoModel();
            $model->agregar((int) $_SESSION['user_id'], $platoId);
        }

        header('Location: /?controller=Carrito&action=index');
        exit;
    }

    // POST: procesar pago ficticio
    public function pagar() {
        $this->requireCliente();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?controller=Carrito&action=index');
            exit;
        }
        verifyCsrf();

        $userId = (int) $_SESSION['user_id'];
        $model  = new CarritoModel();
        $items  = $model->getByUser($userId);

        if (empty($items)) {
            header('Location: /?controller=Carrito&action=index');
            exit;
        }

        $comprasModel = new ComprasModel();
        $compraId = $comprasModel->createCompra($userId, $items);

        $minutos = rand(30, 75);
        $total   = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $items));

        // ── Enviar email de confirmación al cliente ───────────────────────────
        require_once BASE_PATH . 'app/models/UsuarioModel.php';
        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->findById($userId);

        if ($usuario) {
            MailService::enviarConfirmacionCompra(
                $usuario['email'],
                $usuario['name'],
                [
                    'compra_id' => $compraId,
                    'total'     => $total,
                    'items'     => $items,
                ]
            );
        }

        // Guardar en sesión para mostrar en la vista de confirmación
        $_SESSION['pedido_confirmado'] = [
            'compra_id' => $compraId,
            'minutos'   => $minutos,
            'items'     => $items,
            'total'     => $total,
        ];

        // Vaciar el carrito
        $model->vaciar($userId);

        header('Location: /?controller=Carrito&action=confirmacion');
        exit;
    }

    // GET: pantalla de confirmación del pedido
    public function confirmacion() {
        $this->requireCliente();

        if (empty($_SESSION['pedido_confirmado'])) {
            header('Location: /');
            exit;
        }

        $pedido = $_SESSION['pedido_confirmado'];
        unset($_SESSION['pedido_confirmado']); // consumir una sola vez
        require_once BASE_PATH . 'app/views/confirmacion.php';
    }

    // POST: eliminar un plato del carrito
    public function eliminar() {
        $this->requireCliente();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?controller=Carrito&action=index');
            exit;
        }
        verifyCsrf();

        $platoId = isset($_POST['plato_id']) ? (int) $_POST['plato_id'] : 0;
        if ($platoId > 0) {
            $model = new CarritoModel();
            $model->eliminar((int) $_SESSION['user_id'], $platoId);
        }

        header('Location: /?controller=Carrito&action=index');
        exit;
    }
}
