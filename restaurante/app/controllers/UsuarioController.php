<?php
// Archivo: app/controllers/UsuarioController.php

require_once BASE_PATH . 'app/Models/UsuarioModel.php';

class UsuarioController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }

    public function registro() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = 'cliente';

            if ($name === '' || $email === '' || $password === '') {
                $this->renderRegistro('Completá todos los campos.', $name, $email);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->renderRegistro('El email no tiene un formato válido.', $name, $email);
                return;
            }

            $existingUser = $this->usuarioModel->findByEmail($email);
            if ($existingUser) {
                $this->renderRegistro('Ya existe una cuenta con ese email.', $name, $email);
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $created = $this->usuarioModel->createUser($name, $email, $hashedPassword, $role);

            if (!$created) {
                $this->renderRegistro('No se pudo registrar el usuario. Intentá nuevamente.', $name, $email);
                return;
            }

            header('Location: /?controller=Usuario&action=login&success=1');
            exit;
        }

        $this->renderRegistro();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($email === '' || $password === '') {
                $this->renderLogin('Ingresá email y contraseña.', $email);
                return;
            }

            $user = $this->usuarioModel->findByEmail($email);
            if (!$user || !password_verify($password, $user['password'])) {
                $this->renderLogin('Credenciales inválidas.', $email);
                return;
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'] ?? 'cliente';

            header('Location: /');
            exit;
        }

        $successMessage = isset($_GET['success']) && $_GET['success'] === '1'
            ? 'Registro exitoso. Ahora podés iniciar sesión.'
            : '';

        $this->renderLogin('', '', $successMessage);
    }

    public function logout() {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: /');
        exit;
    }

    private function renderRegistro($error = '', $name = '', $email = '') {
        require BASE_PATH . 'app/views/registro.php';
    }

    private function renderLogin($error = '', $email = '', $success = '') {
        require BASE_PATH . 'app/views/login.php';
    }
}
?>
