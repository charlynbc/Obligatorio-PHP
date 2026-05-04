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
}
