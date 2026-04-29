<?php
// Archivo: app/Models/CarritoModel.php

require_once BASE_PATH . 'app/config/Database.php';

class CarritoModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /** Devuelve los ítems del carrito con datos del plato */
    public function getByUser(int $userId): array {
        $stmt = $this->conn->prepare(
            "SELECT ci.id, ci.cantidad, ci.plato_id,
                    p.nombre, p.precio, p.imagen_url, p.categoria
             FROM carrito_items ci
             JOIN platos p ON p.id = ci.plato_id
             WHERE ci.user_id = :uid
             ORDER BY ci.created_at ASC"
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Agrega un plato al carrito; si ya existe incrementa cantidad */
    public function agregar(int $userId, int $platoId): void {
        $now = date('Y-m-d H:i:s');
        $this->conn->prepare(
            "INSERT INTO carrito_items (user_id, plato_id, cantidad, created_at, updated_at)
             VALUES (:uid, :pid, 1, :now, :now)
             ON CONFLICT(user_id, plato_id)
             DO UPDATE SET cantidad = cantidad + 1, updated_at = :now"
        )->execute([':uid' => $userId, ':pid' => $platoId, ':now' => $now]);
    }

    /** Resta 1 de cantidad; elimina el ítem si llega a 0 */
    public function restar(int $userId, int $platoId): void {
        $now = date('Y-m-d H:i:s');
        $this->conn->prepare(
            "UPDATE carrito_items SET cantidad = cantidad - 1, updated_at = :now
             WHERE user_id = :uid AND plato_id = :pid"
        )->execute([':uid' => $userId, ':pid' => $platoId, ':now' => $now]);

        $this->conn->prepare(
            "DELETE FROM carrito_items WHERE user_id = :uid AND plato_id = :pid AND cantidad <= 0"
        )->execute([':uid' => $userId, ':pid' => $platoId]);
    }

    /** Elimina un ítem completo del carrito */
    public function eliminar(int $userId, int $platoId): void {
        $this->conn->prepare(
            "DELETE FROM carrito_items WHERE user_id = :uid AND plato_id = :pid"
        )->execute([':uid' => $userId, ':pid' => $platoId]);
    }

    /** Vacía todo el carrito del usuario */
    public function vaciar(int $userId): void {
        $this->conn->prepare(
            "DELETE FROM carrito_items WHERE user_id = :uid"
        )->execute([':uid' => $userId]);
    }

    /** Cuenta ítems totales (suma de cantidades) para el badge de navbar */
    public function contarItems(int $userId): int {
        $stmt = $this->conn->prepare(
            "SELECT COALESCE(SUM(cantidad), 0) FROM carrito_items WHERE user_id = :uid"
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
