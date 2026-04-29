<?php
// Archivo: app/Helpers/Auth.php — Utilidades de autenticación y autorización

class Auth {

    public static function isLoggedIn(): bool {
        return !empty($_SESSION['user_id']);
    }

    public static function isAdmin(): bool {
        return self::isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header('Location: /?controller=Usuario&action=login');
            exit;
        }
    }

    public static function requireAdmin(): void {
        if (!self::isAdmin()) {
            http_response_code(403);
            require_once BASE_PATH . 'app/views/403.php';
            exit;
        }
    }

    public static function userId(): ?int {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function userName(): string {
        return $_SESSION['user_name'] ?? '';
    }

    public static function userRole(): string {
        return $_SESSION['user_role'] ?? 'cliente';
    }
}
