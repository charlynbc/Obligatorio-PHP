<?php
// Archivo: public/index.php — Front Controller MVC

// Pasar archivos estáticos directamente (CSS, JS, imágenes) cuando se usa php -S
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . $_SERVER['REQUEST_URI'])) {
    return false;
}

// Sesiones para autenticación básica (registro/login/logout)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define la ruta base del proyecto (un nivel arriba de public/)
define('BASE_PATH', __DIR__ . '/../');

// 1. Requerir la conexión a la base de datos
require_once BASE_PATH . 'app/config/Database.php';

// 2. Capturar qué Controlador y qué Acción pide el usuario a través de la URL.
// Si no piden nada, por defecto cargaremos el "MenuController" y la acción "index" (Homepage)
$controllerName = isset($_GET['controller']) ? $_GET['controller'] . 'Controller' : 'MenuController';
$actionName     = isset($_GET['action'])     ? $_GET['action']                    : 'index';

// 3. Construir la ruta del archivo del controlador que se está pidiendo
$controllerPath = BASE_PATH . 'app/controllers/' . $controllerName . '.php';

// 4. Verificar si ese controlador existe físicamente en nuestras carpetas
if (file_exists($controllerPath)) {
    require_once $controllerPath;

    // Instanciar el controlador (ej. $controller = new MenuController())
    $controller = new $controllerName();

    // Verificar si el método (la acción) existe dentro de ese controlador
    if (method_exists($controller, $actionName)) {
        // Ejecutar la acción
        $controller->$actionName();
    } else {
        echo "Error 404: La acción '$actionName' no existe en el controlador '$controllerName'.";
    }
} else {
    echo "Error 404: El controlador '$controllerName' no fue encontrado.";
}
