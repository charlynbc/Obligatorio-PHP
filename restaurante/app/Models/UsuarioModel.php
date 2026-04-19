<?php
// Archivo: app/models/UsuarioModel.php

require_once BASE_PATH . 'app/config/Database.php';

class UsuarioModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function findByEmail($email) {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($name, $email, $hashedPassword, $role) {
        $query = "INSERT INTO users (name, email, password, role, created_at, updated_at)
                  VALUES (:name, :email, :password, :role, :created_at, :updated_at)";

        $stmt = $this->conn->prepare($query);
        $now = date('Y-m-d H:i:s');

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':created_at', $now);
        $stmt->bindParam(':updated_at', $now);

        return $stmt->execute();
    }
}
?>
