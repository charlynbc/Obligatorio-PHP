<?php
// Archivo: app/controllers/MenuController.php

require_once BASE_PATH . 'app/Models/MenuModel.php';

class MenuController {

    // La acción "index" es la que definimos por defecto en nuestro enrutador principal
    public function index() {
        // 1. Instanciamos el Modelo
        $menuModel = new MenuModel();

        // 2. Le pedimos al modelo que traiga todos los platos
        $menus = $menuModel->getAllMenus();

        // 3. Cargamos la Vista.
        // Como requerimos la vista justo después de declarar $menus,
        // el archivo home.php tendrá acceso completo a esa variable.
        require_once BASE_PATH . 'app/views/home.php';
    }
}
?>
