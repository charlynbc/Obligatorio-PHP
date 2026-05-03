<?php
require_once BASE_PATH . 'app/Models/FavoritoModel.php';
require_once BASE_PATH . 'app/Helpers/Auth.php';

class FavoritoController {
    private FavoritoModel $favorito;

    public function __construct() {
        $this->favorito = new FavoritoModel();
    }

    private function requireValidCsrf(): void {
        $session = $_SESSION['csrf_token'] ?? '';
        $request = $_POST['csrf_token'] ?? '';
        if ($session === '' || !hash_equals($session, $request)) {
            http_response_code(419);
            exit('Token CSRF inválido.');
        }
    }

    /** GET /?controller=Favorito&action=index */
    public function index(): void {
        Auth::requireLogin();
        $userId = Auth::userId();
        $favoritos = $this->favorito->getByUser($userId);
        $csrfToken = $_SESSION['csrf_token'] ?? '';
        require_once BASE_PATH . 'app/views/favoritos/index.php';
    }

    /** POST /?controller=Favorito&action=agregar */
    public function agregar(): void {
        Auth::requireLogin();
        $this->requireValidCsrf();
        $platoId = (int) ($_POST['plato_id'] ?? 0);
        if ($platoId > 0) {
            $this->favorito->agregar(Auth::userId(), $platoId);
        }
        $this->redirigir();
    }

    /** POST /?controller=Favorito&action=eliminar */
    public function eliminar(): void {
        Auth::requireLogin();
        $this->requireValidCsrf();
        $platoId = (int) ($_POST['plato_id'] ?? 0);
        if ($platoId > 0) {
            $this->favorito->eliminar(Auth::userId(), $platoId);
        }
        $this->redirigir();
    }

    private function redirigir(): void {
        $ref = $_SERVER['HTTP_REFERER'] ?? '/';
        if (!str_starts_with($ref, '/') && !str_contains(parse_url($ref, PHP_URL_HOST) ?? '', $_SERVER['HTTP_HOST'] ?? '')) {
            $ref = '/';
        }
        header('Location: ' . $ref);
        exit;
    }
}
