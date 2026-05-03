<?php
require_once BASE_PATH . 'app/config/Database.php';

class FavoritoModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getByUser(int $userId): array {
        $stmt = $this->conn->prepare(
            "SELECT f.id, f.plato_id, p.nombre, p.descripcion, p.precio, p.categoria, p.imagen_url
             FROM favoritos f
             JOIN platos p ON p.id = f.plato_id
             WHERE f.user_id = :uid
             ORDER BY f.created_at DESC"
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isFavorito(int $userId, int $platoId): bool {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM favoritos WHERE user_id = :uid AND plato_id = :pid"
        );
        $stmt->execute([':uid' => $userId, ':pid' => $platoId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function getFavoritoIds(int $userId): array {
        $stmt = $this->conn->prepare(
            "SELECT plato_id FROM favoritos WHERE user_id = :uid"
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function agregar(int $userId, int $platoId): void {
        $now = date('Y-m-d H:i:s');
        $this->conn->prepare(
            "INSERT OR IGNORE INTO favoritos (user_id, plato_id, created_at, updated_at)
             VALUES (:uid, :pid, :now, :now)"
        )->execute([':uid' => $userId, ':pid' => $platoId, ':now' => $now]);
    }

    public function eliminar(int $userId, int $platoId): void {
        $this->conn->prepare(
            "DELETE FROM favoritos WHERE user_id = :uid AND plato_id = :pid"
        )->execute([':uid' => $userId, ':pid' => $platoId]);
    }
}
