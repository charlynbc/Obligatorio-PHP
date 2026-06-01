<?php
// Archivo: app/models/CarritoModel.php

require_once BASE_PATH . 'app/config/Database.php';

class CarritoModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Obtener todos los items del carrito de un usuario con datos del plato
    public function getByUser(int $userId): array {
        $stmt = $this->conn->prepare(
            'SELECT ci.id, ci.cantidad, ci.plato_id,
                    p.nombre, p.precio, p.imagen_url, p.categoria
             FROM carrito_items ci
             JOIN platos p ON p.id = ci.plato_id
             WHERE ci.user_id = :user_id
             ORDER BY ci.created_at ASC'
        );
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Agregar un plato al carrito o aumentar su cantidad si ya existe
    public function agregar(int $userId, int $platoId): void {
        // INSERT OR IGNORE primero
        $stmt = $this->conn->prepare(
            'INSERT OR IGNORE INTO carrito_items (user_id, plato_id, cantidad, created_at, updated_at)
             VALUES (:user_id, :plato_id, 1, datetime("now"), datetime("now"))'
        );
        $stmt->bindParam(':user_id',  $userId,  PDO::PARAM_INT);
        $stmt->bindParam(':plato_id', $platoId, PDO::PARAM_INT);
        $stmt->execute();

        // Si ya existía, incrementar cantidad
        if ($stmt->rowCount() === 0) {
            $upd = $this->conn->prepare(
                'UPDATE carrito_items
                 SET cantidad = cantidad + 1, updated_at = datetime("now")
                 WHERE user_id = :user_id AND plato_id = :plato_id'
            );
            $upd->bindParam(':user_id',  $userId,  PDO::PARAM_INT);
            $upd->bindParam(':plato_id', $platoId, PDO::PARAM_INT);
            $upd->execute();
        }
    }

    // Restar una unidad; si llega a 0, eliminar el item
    public function restar(int $userId, int $platoId): void {
        $stmt = $this->conn->prepare(
            'UPDATE carrito_items
             SET cantidad = cantidad - 1, updated_at = datetime("now")
             WHERE user_id = :user_id AND plato_id = :plato_id'
        );
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':plato_id', $platoId, PDO::PARAM_INT);
        $stmt->execute();

        $delete = $this->conn->prepare(
            'DELETE FROM carrito_items
             WHERE user_id = :user_id AND plato_id = :plato_id AND cantidad <= 0'
        );
        $delete->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $delete->bindParam(':plato_id', $platoId, PDO::PARAM_INT);
        $delete->execute();
    }

    // Eliminar un item del carrito
    public function eliminar(int $userId, int $platoId): void {
        $stmt = $this->conn->prepare(
            'DELETE FROM carrito_items WHERE user_id = :user_id AND plato_id = :plato_id'
        );
        $stmt->bindParam(':user_id',  $userId,  PDO::PARAM_INT);
        $stmt->bindParam(':plato_id', $platoId, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Vaciar todo el carrito del usuario
    public function vaciar(int $userId): void {
        $stmt = $this->conn->prepare(
            'DELETE FROM carrito_items WHERE user_id = :user_id'
        );
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Contar items totales en el carrito (para el badge de la navbar)
    public function countItems(int $userId): int {
        $stmt = $this->conn->prepare(
            'SELECT COALESCE(SUM(cantidad), 0) FROM carrito_items WHERE user_id = :user_id'
        );
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
