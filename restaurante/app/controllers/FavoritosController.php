<?php
// Archivo: app/controllers/FavoritosController.php

require_once BASE_PATH . 'app/models/FavoritosModel.php';

class FavoritosController {
    private function requireCliente(): void {
        if (!isLoggedIn()) {
            header('Location: /?controller=Usuario&action=login');
            exit;
        }

        if (isAdmin()) {
            http_response_code(403);
            exit('Acceso denegado. Solo los clientes pueden gestionar favoritos.');
        }
    }

    public function toggle() {
        $this->requireCliente();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        verifyCsrf();

        $platoId = isset($_POST['plato_id']) ? (int) $_POST['plato_id'] : 0;
        if ($platoId <= 0) {
            header('Location: /?favoritos_error=id');
            exit;
        }

        $model = new FavoritosModel();
        $isFavorite = $model->toggle((int) $_SESSION['user_id'], $platoId);

        header('Location: /?favoritos_status=' . ($isFavorite ? 'added' : 'removed'));
        exit;
    }
}