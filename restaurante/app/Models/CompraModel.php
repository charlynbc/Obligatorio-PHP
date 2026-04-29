<?php
// Archivo: app/Models/CompraModel.php

require_once BASE_PATH . 'app/config/Database.php';

class CompraModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Crea una compra con sus detalles en una transacción.
     * $items: array de ['plato_id', 'nombre', 'cantidad', 'precio']
     * Devuelve el ID de la compra creada.
     */
    public function crear(int $userId, array $items): int {
        $total = array_reduce($items, fn($sum, $i) => $sum + ($i['precio'] * $i['cantidad']), 0);
        $now   = date('Y-m-d H:i:s');

        $this->conn->beginTransaction();

        $stmt = $this->conn->prepare(
            "INSERT INTO compras (user_id, total, created_at, updated_at)
             VALUES (:uid, :total, :now, :now)"
        );
        $stmt->execute([':uid' => $userId, ':total' => $total, ':now' => $now]);
        $compraId = (int) $this->conn->lastInsertId();

        $detalle = $this->conn->prepare(
            "INSERT INTO compra_detalles (compra_id, plato_id, cantidad, precio_unitario, created_at, updated_at)
             VALUES (:cid, :pid, :qty, :precio, :now, :now)"
        );
        foreach ($items as $item) {
            $detalle->execute([
                ':cid'    => $compraId,
                ':pid'    => (int) $item['plato_id'],
                ':qty'    => (int) $item['cantidad'],
                ':precio' => (float) $item['precio'],
                ':now'    => $now,
            ]);
        }

        $this->conn->commit();
        return $compraId;
    }

    /** Historial de compras del usuario, la más reciente primero */
    public function historialByUser(int $userId): array {
        $stmt = $this->conn->prepare(
            "SELECT c.id, c.total, c.created_at,
                    GROUP_CONCAT(p.nombre || ' x' || cd.cantidad, ', ') AS detalle
             FROM compras c
             JOIN compra_detalles cd ON cd.compra_id = c.id
             JOIN platos p ON p.id = cd.plato_id
             WHERE c.user_id = :uid
             GROUP BY c.id
             ORDER BY c.created_at DESC"
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
