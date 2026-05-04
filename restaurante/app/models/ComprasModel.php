<?php
// Archivo: app/models/ComprasModel.php

require_once BASE_PATH . 'app/config/Database.php';

class ComprasModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function createCompra(int $userId, array $items): int {
        $total = array_sum(array_map(fn($item) => (float) $item['precio'] * (int) $item['cantidad'], $items));

        $this->conn->beginTransaction();

        try {
            $stmt = $this->conn->prepare(
                'INSERT INTO compras (user_id, total, created_at, updated_at)
                 VALUES (:user_id, :total, datetime("now"), datetime("now"))'
            );
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':total', $total);
            $stmt->execute();

            $compraId = (int) $this->conn->lastInsertId();

            $detailStmt = $this->conn->prepare(
                'INSERT INTO compra_detalles (compra_id, plato_id, cantidad, precio_unitario, created_at, updated_at)
                 VALUES (:compra_id, :plato_id, :cantidad, :precio_unitario, datetime("now"), datetime("now"))'
            );

            foreach ($items as $item) {
                $platoId = (int) $item['plato_id'];
                $cantidad = (int) $item['cantidad'];
                $precioUnitario = (float) $item['precio'];
                $detailStmt->bindParam(':compra_id', $compraId, PDO::PARAM_INT);
                $detailStmt->bindParam(':plato_id', $platoId, PDO::PARAM_INT);
                $detailStmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
                $detailStmt->bindParam(':precio_unitario', $precioUnitario);
                $detailStmt->execute();
            }

            $this->conn->commit();
            return $compraId;
        } catch (Throwable $exception) {
            $this->conn->rollBack();
            throw $exception;
        }
    }

    public function getByUser(int $userId): array {
        $stmt = $this->conn->prepare(
            'SELECT c.id AS compra_id,
                    c.total,
                    c.created_at AS compra_fecha,
                    cd.cantidad,
                    cd.precio_unitario,
                    p.nombre,
                    p.categoria,
                    p.imagen_url
             FROM compras c
             JOIN compra_detalles cd ON cd.compra_id = c.id
             JOIN platos p ON p.id = cd.plato_id
             WHERE c.user_id = :user_id
             ORDER BY c.created_at DESC, cd.id ASC'
        );
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $compras = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $compraId = (int) $row['compra_id'];
            if (!isset($compras[$compraId])) {
                $compras[$compraId] = [
                    'id' => $compraId,
                    'fecha' => $row['compra_fecha'],
                    'total' => (float) $row['total'],
                    'items' => [],
                ];
            }

            $compras[$compraId]['items'][] = [
                'nombre' => $row['nombre'],
                'categoria' => $row['categoria'],
                'imagen_url' => $row['imagen_url'],
                'cantidad' => (int) $row['cantidad'],
                'precio_unitario' => (float) $row['precio_unitario'],
            ];
        }

        return array_values($compras);
    }

    public function getTopSellingPlatos(int $limit = 5): array {
        $stmt = $this->conn->prepare(
            'SELECT p.nombre,
                    p.categoria,
                    SUM(cd.cantidad) AS unidades_vendidas,
                    SUM(cd.cantidad * cd.precio_unitario) AS total_facturado,
                    COUNT(DISTINCT cd.compra_id) AS compras
             FROM compra_detalles cd
             JOIN platos p ON p.id = cd.plato_id
             GROUP BY cd.plato_id, p.nombre, p.categoria
             ORDER BY unidades_vendidas DESC, total_facturado DESC, p.nombre ASC
             LIMIT :limit'
        );
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesSummary(): array {
        $stmt = $this->conn->query(
            'SELECT COALESCE(SUM(CASE WHEN u.role = "local" THEN c.total ELSE 0 END), 0) AS total_local,
                    COALESCE(SUM(CASE WHEN u.role != "local" THEN c.total ELSE 0 END), 0) AS total_online,
                    COALESCE(SUM(c.total), 0) AS total_general,
                    COALESCE(SUM(CASE WHEN u.role = "local" THEN 1 ELSE 0 END), 0) AS ventas_locales,
                    COALESCE(SUM(CASE WHEN u.role != "local" THEN 1 ELSE 0 END), 0) AS ventas_online
             FROM compras c
             JOIN users u ON u.id = c.user_id'
        );
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_local' => (float) ($row['total_local'] ?? 0),
            'total_online' => (float) ($row['total_online'] ?? 0),
            'total_general' => (float) ($row['total_general'] ?? 0),
            'ventas_locales' => (int) ($row['ventas_locales'] ?? 0),
            'ventas_online' => (int) ($row['ventas_online'] ?? 0),
        ];
    }
}