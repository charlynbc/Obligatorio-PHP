<?php
// Archivo: app/controllers/MenuController.php

require_once BASE_PATH . 'app/models/MenuModel.php';

class MenuController {

    // La acción "index" es la que definimos por defecto en nuestro enrutador principal
    public function index() {
        $menuModel = new MenuModel();
        $menus = $menuModel->getAllMenus();
        require_once BASE_PATH . 'app/views/home.php';
    }

    // Acción para subir/actualizar la imagen de un plato (solo admin)
    public function upload() {
        // ── Control de acceso: solo admin ─────────────────────────────────────
        if (!isAdmin()) {
            http_response_code(403);
            exit('Acceso denegado. Solo los administradores pueden subir imágenes.');
        }

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
        $filename   = 'plato_' . $id . '_' . time() . '.' . $safeExt;
        $uploadDir  = BASE_PATH . 'public/img/';
        $uploadPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            header('Location: /?upload_error=guardado');
            exit;
        }

        $imageUrl  = '/img/' . $filename;
        $menuModel = new MenuModel();
        $menuModel->updateImageUrl($id, $imageUrl);

        header('Location: /');
        exit;
    }
}
?>
