<?php
// Archivo: app/controllers/MenuController.php

require_once BASE_PATH . 'app/models/MenuModel.php';
require_once BASE_PATH . 'app/models/ComprasModel.php';
require_once BASE_PATH . 'app/models/UsuarioModel.php';

class MenuController {

    private function getMenuCategories(): array {
        return [
            'Entradas',
            'Ensaladas',
            'Pastas',
            'Pizzas',
            'Principales',
            'Postres',
            'Bebidas sin alcohol',
            'Bebidas con alcohol',
        ];
    }

    private function requireAdmin(): void {
        if (!isAdmin()) {
            http_response_code(403);
            exit('Acceso denegado. Solo los administradores pueden realizar esta acción.');
        }
    }

    private function getValidatedMenuInput(): array {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $precio = str_replace(',', '.', trim((string) ($_POST['precio'] ?? '')));

        if ($nombre === '' || $descripcion === '' || $categoria === '' || $precio === '') {
            header('Location: /?menu_error=campos');
            exit;
        }

        if (!is_numeric($precio) || (float) $precio <= 0) {
            header('Location: /?menu_error=precio');
            exit;
        }

        if (!in_array($categoria, $this->getMenuCategories(), true)) {
            header('Location: /?menu_error=categoria');
            exit;
        }

        return [$nombre, $descripcion, (float) $precio, $categoria];
    }

    // La acción "index" es la que definimos por defecto en nuestro enrutador principal
    public function index() {
        $menuModel = new MenuModel();
        $sort = $_GET['sort'] ?? 'categoria_asc';
        $menus = $menuModel->getAllMenus($sort);
        $menuCategories = $this->getMenuCategories();
        $editPlato = null;
        $topSelling = [];
        $salesSummary = [
            'total_local' => 0,
            'total_online' => 0,
            'total_general' => 0,
            'ventas_locales' => 0,
            'ventas_online' => 0,
        ];

        if (isAdmin() && isset($_GET['edit_plato'])) {
            $editPlatoId = (int) $_GET['edit_plato'];
            if ($editPlatoId > 0) {
                $editPlato = $menuModel->findById($editPlatoId);
            }
        }

        if (isAdmin()) {
            $comprasModel = new ComprasModel();
            $topSelling = $comprasModel->getTopSellingPlatos();
            $salesSummary = $comprasModel->getSalesSummary();
        }

        require_once BASE_PATH . 'app/views/home.php';
    }

    public function localSale() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        verifyCsrf();

        $menuModel = new MenuModel();
        $menus = $menuModel->getAllMenus();
        $items = [];

        foreach ($menus as $plato) {
            $cantidad = isset($_POST['cantidad_' . $plato['id']]) ? (int) $_POST['cantidad_' . $plato['id']] : 0;
            if ($cantidad > 0) {
                $items[] = [
                    'plato_id' => (int) $plato['id'],
                    'cantidad' => $cantidad,
                    'precio' => (float) $plato['precio'],
                ];
            }
        }

        if (empty($items)) {
            header('Location: /?local_error=sin_items');
            exit;
        }

        $usuarioModel = new UsuarioModel();
        $localUserId = $usuarioModel->getOrCreateLocalSaleUserId();

        $comprasModel = new ComprasModel();
        $comprasModel->createCompra($localUserId, $items);

        header('Location: /?local_status=ok');
        exit;
    }

    public function create() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        verifyCsrf();
        [$nombre, $descripcion, $precio, $categoria] = $this->getValidatedMenuInput();

        $menuModel = new MenuModel();
        $menuModel->create($nombre, $descripcion, $precio, $categoria);

        header('Location: /?menu_status=created');
        exit;
    }

    public function update() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        verifyCsrf();

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            header('Location: /?menu_error=id');
            exit;
        }

        [$nombre, $descripcion, $precio, $categoria] = $this->getValidatedMenuInput();

        $menuModel = new MenuModel();
        if (!$menuModel->findById($id)) {
            header('Location: /?menu_error=noexiste');
            exit;
        }

        $menuModel->update($id, $nombre, $descripcion, $precio, $categoria);

        header('Location: /?menu_status=updated');
        exit;
    }

    public function delete() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        verifyCsrf();

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            header('Location: /?menu_error=id');
            exit;
        }

        $menuModel = new MenuModel();
        $plato = $menuModel->findById($id);
        if (!$plato) {
            header('Location: /?menu_error=noexiste');
            exit;
        }

        $menuModel->delete($id);

        if (!empty($plato['imagen_url']) && str_starts_with($plato['imagen_url'], '/img/')) {
            $imagePath = BASE_PATH . 'public' . $plato['imagen_url'];
            if (is_file($imagePath)) {
                @unlink($imagePath);
            }
        }

        header('Location: /?menu_status=deleted');
        exit;
    }

    // Acción para subir/actualizar la imagen de un plato (solo admin)
    public function upload() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        // ── Verificar token CSRF ──────────────────────────────────────────────
        verifyCsrf();

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            header('Location: /');
            exit;
        }

        $menuModel = new MenuModel();
        $plato = $menuModel->findById($id);
        if (!$plato) {
            header('Location: /?menu_error=noexiste');
            exit;
        }

        if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            header('Location: /?upload_error=1');
            exit;
        }

        $file = $_FILES['imagen'];

        // Validar tipo MIME real del archivo (no confiar en extensión ni Content-Type)
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $extMap   = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];

        if (!array_key_exists($mimeType, $extMap)) {
            header('Location: /?upload_error=tipo');
            exit;
        }

        // Limitar tamaño a 5 MB
        if ($file['size'] > 5 * 1024 * 1024) {
            header('Location: /?upload_error=tamano');
            exit;
        }

        $safeExt    = $extMap[$mimeType];
        $filename   = 'plato_' . $id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $safeExt;
        $uploadDir  = BASE_PATH . 'public/img/';
        $uploadPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            header('Location: /?upload_error=guardado');
            exit;
        }

        if (!empty($plato['imagen_url']) && str_starts_with($plato['imagen_url'], '/img/')) {
            $oldImagePath = BASE_PATH . 'public' . $plato['imagen_url'];
            if ($oldImagePath !== $uploadPath && is_file($oldImagePath)) {
                @unlink($oldImagePath);
            }
        }

        $imageUrl  = '/img/' . $filename;
        $menuModel->updateImageUrl($id, $imageUrl);

        header('Location: /');
        exit;
    }
}
?>
