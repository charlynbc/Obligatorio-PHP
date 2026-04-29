<?php
// Archivo: app/controllers/MenuController.php

require_once BASE_PATH . 'app/Models/MenuModel.php';

class MenuController {

    private $menuModel;

    public function __construct() {
        $this->menuModel = new MenuModel();
    }

    private function requireAdmin() {
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            header('Location: /?controller=Usuario&action=login');
            exit;
        }
    }

    private function requireValidCsrfToken() {
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        $requestToken = $_POST['csrf_token'] ?? '';

        if (!is_string($sessionToken) || !is_string($requestToken) || $sessionToken === '' || !hash_equals($sessionToken, $requestToken)) {
            http_response_code(419);
            echo 'Sesión expirada o token CSRF inválido.';
            exit;
        }
    }

    // La acción "index" es la que definimos por defecto en nuestro enrutador principal
    public function index() {
        // 1. Le pedimos al modelo que traiga todos los platos
        $menus = $this->menuModel->getAllMenus();

        // 2. Cargamos la Vista.
        // Como requerimos la vista justo después de declarar $menus,
        // el archivo home.php tendrá acceso completo a esa variable.
        require_once BASE_PATH . 'app/views/home.php';
    }

    public function crear() {
        $this->requireAdmin();

        $error = '';
        $menu = [
            'nombre' => '',
            'descripcion' => '',
            'precio' => '',
            'categoria' => '',
            'imagen_url' => '',
        ];

        require_once BASE_PATH . 'app/views/menu/crear.php';
    }

    public function guardar() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?controller=Menu&action=crear');
            exit;
        }

        $this->requireValidCsrfToken();

        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = trim($_POST['precio'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $imagenUrl = trim($_POST['imagen_url'] ?? '');

        if ($nombre === '' || $descripcion === '' || $precio === '' || !is_numeric($precio) || (float) $precio < 0) {
            $error = 'Completá nombre, descripción y un precio válido.';
            $menu = [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'precio' => $precio,
                'categoria' => $categoria,
                'imagen_url' => $imagenUrl,
            ];
            require_once BASE_PATH . 'app/views/menu/crear.php';
            return;
        }

        $created = $this->menuModel->createMenu($nombre, $descripcion, $precio, $categoria, $imagenUrl);
        if (!$created) {
            $error = 'No se pudo crear el plato. Intentá nuevamente.';
            $menu = [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'precio' => $precio,
                'categoria' => $categoria,
                'imagen_url' => $imagenUrl,
            ];
            require_once BASE_PATH . 'app/views/menu/crear.php';
            return;
        }

        header('Location: /?menu_success=created');
        exit;
    }

    public function editar() {
        $this->requireAdmin();

        $id = $_GET['id'] ?? null;
        if ($id === null || !ctype_digit((string) $id)) {
            http_response_code(400);
            echo 'ID de plato inválido.';
            return;
        }

        $menu = $this->menuModel->findById((int) $id);
        if (!$menu) {
            http_response_code(404);
            echo 'Plato no encontrado.';
            return;
        }

        $error = '';
        require_once BASE_PATH . 'app/views/menu/editar.php';
    }

    public function actualizar() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        $this->requireValidCsrfToken();

        $id = $_POST['id'] ?? null;
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = trim($_POST['precio'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $imagenUrl = trim($_POST['imagen_url'] ?? '');

        if ($id === null || !ctype_digit((string) $id)) {
            http_response_code(400);
            echo 'ID de plato inválido.';
            return;
        }

        if ($nombre === '' || $descripcion === '' || $precio === '' || !is_numeric($precio) || (float) $precio < 0) {
            $error = 'Completá nombre, descripción y un precio válido.';
            $menu = [
                'id' => (int) $id,
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'precio' => $precio,
                'categoria' => $categoria,
                'imagen_url' => $imagenUrl,
            ];
            require_once BASE_PATH . 'app/views/menu/editar.php';
            return;
        }

        $updated = $this->menuModel->updateMenu((int) $id, $nombre, $descripcion, $precio, $categoria, $imagenUrl);
        if (!$updated) {
            $error = 'No se pudo actualizar el plato. Intentá nuevamente.';
            $menu = [
                'id' => (int) $id,
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'precio' => $precio,
                'categoria' => $categoria,
                'imagen_url' => $imagenUrl,
            ];
            require_once BASE_PATH . 'app/views/menu/editar.php';
            return;
        }

        header('Location: /?menu_success=updated');
        exit;
    }

    public function eliminar() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        $this->requireValidCsrfToken();

        $id = $_POST['id'] ?? null;
        if ($id === null || !ctype_digit((string) $id)) {
            http_response_code(400);
            echo 'ID de plato inválido.';
            return;
        }

        $this->menuModel->deleteMenu((int) $id);
        header('Location: /?menu_success=deleted');
        exit;
    }
}
?>
