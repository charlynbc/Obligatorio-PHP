<?php
// Archivo: app/models/UsuarioModel.php

require_once BASE_PATH . 'app/config/Database.php';

class UsuarioModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Buscar usuario por email (para login)
    public function findByEmail(string $email): ?array {
        $stmt = $this->conn->prepare(
            'SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Buscar usuario por ID (para perfil)
    public function findById(int $id): ?array {
        $stmt = $this->conn->prepare(
            'SELECT id, name, email, role FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getAdmins(): array {
        $stmt = $this->conn->prepare(
            'SELECT id, name, email, role FROM users WHERE role = :role ORDER BY name ASC, email ASC'
        );
        $role = 'admin';
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrCreateLocalSaleUserId(): int {
        $email = 'ventas.local@restaurante.internal';

        $stmt = $this->conn->prepare(
            'SELECT id FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $existingId = $stmt->fetchColumn();

        if ($existingId !== false) {
            return (int) $existingId;
        }

        $name = 'Venta en Local';
        $passwordHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
        $role = 'local';
        $this->create($name, $email, $passwordHash, $role);

        return (int) $this->conn->lastInsertId();
    }

    // Actualizar nombre (y opcionalmente contraseña) — el email nunca se modifica
    public function updateProfile(int $id, string $name, ?string $newPasswordHash): void {
        if ($newPasswordHash !== null) {
            $stmt = $this->conn->prepare(
                'UPDATE users SET name = :name, password = :password, updated_at = datetime("now") WHERE id = :id'
            );
            $stmt->bindParam(':password', $newPasswordHash);
        } else {
            $stmt = $this->conn->prepare(
                'UPDATE users SET name = :name, updated_at = datetime("now") WHERE id = :id'
            );
        }
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':id',   $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Crear un nuevo usuario
    public function create(string $name, string $email, string $passwordHash, string $role): void {
        $stmt = $this->conn->prepare(
            'INSERT INTO users (name, email, password, role, created_at, updated_at)
             VALUES (:name, :email, :password, :role, datetime("now"), datetime("now"))'
        );
        $stmt->bindParam(':name',     $name);
        $stmt->bindParam(':email',    $email);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':role',     $role);
        $stmt->execute();
    }

    public function deleteById(int $id): void {
        $stmt = $this->conn->prepare('DELETE FROM users WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
