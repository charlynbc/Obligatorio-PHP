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
    public function getAllMenus(string $sort = 'default') {
        $categoryOrder = "CASE categoria
            WHEN 'Entradas' THEN 1
            WHEN 'Ensaladas' THEN 2
            WHEN 'Pastas' THEN 3
            WHEN 'Pizzas' THEN 4
            WHEN 'Principales' THEN 5
            WHEN 'Postres' THEN 6
            WHEN 'Bebidas sin alcohol' THEN 7
            WHEN 'Bebidas con alcohol' THEN 8
            WHEN 'Bebidas' THEN 7
            ELSE 99
        END";

        $orderBy = match ($sort) {
            'categoria_asc' => ' ORDER BY ' . $categoryOrder . ' ASC, nombre ASC, id ASC',
            'categoria_desc' => ' ORDER BY ' . $categoryOrder . ' DESC, nombre ASC, id ASC',
            'categoria_nombre_asc' => ' ORDER BY categoria COLLATE NOCASE ASC, nombre ASC, id ASC',
            'categoria_nombre_desc' => ' ORDER BY categoria COLLATE NOCASE DESC, nombre ASC, id ASC',
            'precio_asc' => ' ORDER BY precio ASC, ' . $categoryOrder . ' ASC, nombre ASC',
            'precio_desc' => ' ORDER BY precio DESC, ' . $categoryOrder . ' ASC, nombre ASC',
            'nombre_asc' => ' ORDER BY nombre ASC, ' . $categoryOrder . ' ASC',
            'nombre_desc' => ' ORDER BY nombre DESC, ' . $categoryOrder . ' ASC',
            default => ' ORDER BY ' . $categoryOrder . ' ASC, nombre ASC, id ASC',
        };

        $query = 'SELECT * FROM platos' . $orderBy;

        // Preparamos la consulta (buena práctica de seguridad con PDO)
        $stmt = $this->conn->prepare($query);

        // Ejecutamos la consulta
        $stmt->execute();

        // FETCH_ASSOC devuelve los datos como arreglo asociativo
        // (ej: $fila['nombre'] en lugar de $fila[1])
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $stmt = $this->conn->prepare('SELECT * FROM platos WHERE id = :id LIMIT 1');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(string $nombre, string $descripcion, float $precio, string $categoria): void {
        $stmt = $this->conn->prepare(
            'INSERT INTO platos (nombre, descripcion, precio, categoria, created_at, updated_at)
             VALUES (:nombre, :descripcion, :precio, :categoria, datetime("now"), datetime("now"))'
        );
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->execute();
    }

    public function update(int $id, string $nombre, string $descripcion, float $precio, string $categoria): void {
        $stmt = $this->conn->prepare(
            'UPDATE platos
             SET nombre = :nombre,
                 descripcion = :descripcion,
                 precio = :precio,
                 categoria = :categoria,
                 updated_at = datetime("now")
             WHERE id = :id'
        );
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->execute();
    }

    public function delete(int $id): void {
        $stmt = $this->conn->prepare('DELETE FROM platos WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Método para actualizar la URL de imagen de un plato
    public function updateImageUrl($id, $imageUrl) {
        $stmt = $this->conn->prepare(
            "UPDATE platos SET imagen_url = :imagen_url WHERE id = :id"
        );
        $stmt->bindParam(':imagen_url', $imageUrl);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
?>
