<?php
require_once BASE_PATH . 'app/Models/CompraModel.php';
require_once BASE_PATH . 'app/Helpers/Auth.php';

class CompraController {
    private CompraModel $compra;

    public function __construct() {
        $this->compra = new CompraModel();
    }

    /** GET /?controller=Compra&action=historial */
    public function historial(): void {
        Auth::requireLogin();
        $userId = Auth::userId();
        $compras = $this->compra->historialByUser($userId);
        require_once BASE_PATH . 'app/views/compras/historial.php';
    }
}
