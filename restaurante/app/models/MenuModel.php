<?php
// Archivo: app/Models/MenuModel.php

require_once BASE_PATH . 'app/config/Database.php';

class MenuModel {
    private $conn;

    // El constructor se ejecuta automáticamente cada vez que llamamos a esta clase
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllMenus(string $orderBy = 'id'): array {
        $allowed = [
            'precio_asc'  => 'precio ASC',
            'precio_desc' => 'precio DESC',
            'nombre_asc'  => 'nombre ASC',
            'nombre_desc' => 'nombre DESC',
        ];

        $orderClause = $allowed[$orderBy] ?? 'id ASC';
        $query = "SELECT * FROM platos ORDER BY " . $orderClause;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $query = "SELECT * FROM platos WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createMenu($nombre, $descripcion, $precio, $categoria, $imagenUrl) {
        $query = "INSERT INTO platos (nombre, descripcion, precio, categoria, imagen_url, created_at, updated_at)
                  VALUES (:nombre, :descripcion, :precio, :categoria, :imagen_url, :created_at, :updated_at)";

        $stmt = $this->conn->prepare($query);
        $now = date('Y-m-d H:i:s');

        $stmt->bindValue(':nombre', $nombre);
        $stmt->bindValue(':descripcion', $descripcion);
        $stmt->bindValue(':precio', (float) $precio);
        $stmt->bindValue(':categoria', $categoria !== '' ? $categoria : null);
        $stmt->bindValue(':imagen_url', $imagenUrl !== '' ? $imagenUrl : null);
        $stmt->bindValue(':created_at', $now);
        $stmt->bindValue(':updated_at', $now);

        return $stmt->execute();
    }

    public function updateMenu($id, $nombre, $descripcion, $precio, $categoria, $imagenUrl) {
        $query = "UPDATE platos
                  SET nombre = :nombre,
                      descripcion = :descripcion,
                      precio = :precio,
                      categoria = :categoria,
                      imagen_url = :imagen_url,
                      updated_at = :updated_at
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $nombre);
        $stmt->bindValue(':descripcion', $descripcion);
        $stmt->bindValue(':precio', (float) $precio);
        $stmt->bindValue(':categoria', $categoria !== '' ? $categoria : null);
        $stmt->bindValue(':imagen_url', $imagenUrl !== '' ? $imagenUrl : null);
        $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'));

        return $stmt->execute();
    }

    public function deleteMenu($id) {
        $query = "DELETE FROM platos WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
?>
