<?php
// Archivo: app/models/FavoritosModel.php

require_once BASE_PATH . 'app/config/Database.php';

class FavoritosModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getFavoriteIdsByUser(int $userId): array {
        $stmt = $this->conn->prepare(
            'SELECT plato_id FROM favoritos WHERE user_id = :user_id'
        );
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public function exists(int $userId, int $platoId): bool {
        $stmt = $this->conn->prepare(
            'SELECT 1 FROM favoritos WHERE user_id = :user_id AND plato_id = :plato_id LIMIT 1'
        );
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':plato_id', $platoId, PDO::PARAM_INT);
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    public function add(int $userId, int $platoId): void {
        $stmt = $this->conn->prepare(
            'INSERT OR IGNORE INTO favoritos (user_id, plato_id, created_at, updated_at)
             VALUES (:user_id, :plato_id, datetime("now"), datetime("now"))'
        );
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':plato_id', $platoId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function remove(int $userId, int $platoId): void {
        $stmt = $this->conn->prepare(
            'DELETE FROM favoritos WHERE user_id = :user_id AND plato_id = :plato_id'
        );
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':plato_id', $platoId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function toggle(int $userId, int $platoId): bool {
        if ($this->exists($userId, $platoId)) {
            $this->remove($userId, $platoId);
            return false;
        }

        $this->add($userId, $platoId);
        return true;
    }

    public function getByUser(int $userId): array {
        $stmt = $this->conn->prepare(
            'SELECT p.id, p.nombre, p.descripcion, p.precio, p.categoria, p.imagen_url, f.created_at
             FROM favoritos f
             JOIN platos p ON p.id = f.plato_id
             WHERE f.user_id = :user_id
             ORDER BY f.created_at DESC'
        );
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}