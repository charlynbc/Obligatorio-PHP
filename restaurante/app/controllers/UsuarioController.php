<?php
// Archivo: app/controllers/UsuarioController.php

require_once BASE_PATH . 'app/models/UsuarioModel.php';

class UsuarioController {

    // GET: mostrar formulario | POST: procesar login
    public function login() {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();

            $email    = trim($_POST['email']    ?? '');
            $password =      $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = 'Completá todos los campos.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email inválido.';
            } else {
                $model = new UsuarioModel();
                $user  = $model->findByEmail($email);

                if ($user && password_verify($password, $user['password'])) {
                    // Regenerar ID de sesión para prevenir Session Fixation
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name']    = $user['name'];
                    $_SESSION['role']    = $user['role'];
                    header('Location: /');
                    exit;
                } else {
                    // Mensaje genérico para no revelar si el email existe
                    $error = 'Email o contraseña incorrectos.';
                }
            }
        }

        require_once BASE_PATH . 'app/views/login.php';
    }

    // GET: mostrar formulario | POST: procesar registro
    public function registro() {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();

            $name     = trim($_POST['name']     ?? '');
            $email    = trim($_POST['email']    ?? '');
            $password =      $_POST['password'] ?? '';
            $confirm  =      $_POST['confirm']  ?? '';

            if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
                $error = 'Completá todos los campos.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'El email ingresado no es válido.';
            } elseif (strlen($password) < 8) {
                $error = 'La contraseña debe tener al menos 8 caracteres.';
            } elseif ($password !== $confirm) {
                $error = 'Las contraseñas no coinciden.';
            } else {
                $model = new UsuarioModel();
                if ($model->findByEmail($email)) {
                    $error = 'Ya existe una cuenta con ese email.';
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $model->create($name, $email, $hash, 'cliente');
                    header('Location: /?controller=Usuario&action=login&registered=1');
                    exit;
                }
            }
        }

        require_once BASE_PATH . 'app/views/registro.php';
    }

    // GET: mostrar formulario de perfil | POST: guardar cambios
    public function perfil() {
        // Solo usuarios autenticados
        if (!isLoggedIn()) {
            header('Location: /?controller=Usuario&action=login');
            exit;
        }

        $model   = new UsuarioModel();
        $user    = $model->findById((int) $_SESSION['user_id']);
        $success = '';
        $error   = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();

            $name            = trim($_POST['name']             ?? '');
            $currentPassword =      $_POST['current_password'] ?? '';
            $newPassword     =      $_POST['new_password']     ?? '';
            $confirmPassword =      $_POST['confirm_password'] ?? '';

            if (empty($name)) {
                $error = 'El nombre no puede estar vacío.';
            } elseif (!empty($newPassword) || !empty($confirmPassword)) {
                // Si quiere cambiar contraseña, validar la actual primero
                $fullUser = $model->findByEmail($user['email']);
                if (!password_verify($currentPassword, $fullUser['password'])) {
                    $error = 'La contraseña actual es incorrecta.';
                } elseif (strlen($newPassword) < 8) {
                    $error = 'La nueva contraseña debe tener al menos 8 caracteres.';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'Las contraseñas nuevas no coinciden.';
                } else {
                    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                    $model->updateProfile((int) $_SESSION['user_id'], $name, $hash);
                    $_SESSION['name'] = $name;
                    $user['name']     = $name;
                    $success = 'Datos actualizados correctamente.';
                }
            } else {
                // Solo cambiar nombre
                $model->updateProfile((int) $_SESSION['user_id'], $name, null);
                $_SESSION['name'] = $name;
                $user['name']     = $name;
                $success = 'Datos actualizados correctamente.';
            }
        }

        require_once BASE_PATH . 'app/views/perfil.php';
    }

    // POST: cerrar sesión de forma segura
    public function logout() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        verifyCsrf();

        // Vaciar datos de sesión
        $_SESSION = [];

        // Eliminar la cookie de sesión
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
        header('Location: /');
        exit;
    }
}
