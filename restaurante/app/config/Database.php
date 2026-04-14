<?php
// Archivo: app/config/Database.php

class Database {
    // Ruta al archivo SQLite (relativa a la raíz del proyecto)
    private $db_path;
    public $conn;

    public function __construct() {
        // BASE_PATH se define en public/index.php
        $this->db_path = BASE_PATH . 'database/database.sqlite';
    }

    // Método para obtener la conexión
    public function getConnection() {
        $this->conn = null;

        try {
            // Conexión PDO a SQLite — no requiere usuario ni contraseña
            $this->conn = new PDO("sqlite:" . $this->db_path);

            // Forzamos a PDO a que lance excepciones si hay errores (ideal para depurar)
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            // Si la conexión falla, detenemos todo y mostramos el error
            echo "Error de conexión a la Base de Datos: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
