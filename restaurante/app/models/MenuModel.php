<?php
// Archivo: app/models/MenuModel.php

require_once BASE_PATH . 'app/config/Database.php';

class MenuModel {
    private $conn;

    // El constructor se ejecuta automáticamente cada vez que llamamos a esta clase
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Método para obtener todos los platos del menú
    public function getAllMenus() {
        // Tip: se puede agregar "ORDER BY precio ASC" para el requisito opcional de ordenar
        $query = "SELECT * FROM platos";

        // Preparamos la consulta (buena práctica de seguridad con PDO)
        $stmt = $this->conn->prepare($query);

        // Ejecutamos la consulta
        $stmt->execute();

        // FETCH_ASSOC devuelve los datos como arreglo asociativo
        // (ej: $fila['nombre'] en lugar de $fila[1])
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
