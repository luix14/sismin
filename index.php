<?php
session_start();

// Verificar inclusión de config.php
if (!file_exists('config.php')) {
    error_log('Archivo config.php no encontrado en ' . __DIR__);
    die('Error: Archivo de configuración no encontrado');
}
require_once 'config.php';

// Verificar inclusión de db.php
if (!file_exists('includes/db.php')) {
    error_log('Archivo db.php no encontrado en ' . __DIR__ . '/includes');
    die('Error: Archivo de conexión a la base de datos no encontrado');
}
require_once 'includes/db.php';

require_once 'vendor/autoload.php';

// Verificar que $pdo esté definido
if (!isset($pdo) || !$pdo instanceof PDO) {
    error_log('Variable $pdo no definida o no es una instancia de PDO');
    die('Error: Conexión a la base de datos no inicializada');
}
error_log('Conexión a la base de datos cargada desde db.php');

// Cargar rutas
if (!file_exists('includes/routes.php')) {
    error_log('Archivo routes.php no encontrado en ' . __DIR__ . '/includes');
    die('Error: Archivo de rutas no encontrado');
}
require_once 'includes/routes.php';

// Obtener la ruta solicitada
$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
$baseUrl = BASE_URL;
$request = str_replace($baseUrl, '/sismin', $request);
$request = parse_url($request, PHP_URL_PATH);
error_log("Procesando solicitud: $request ($method)");

// Buscar la ruta correspondiente
$controllerName = null;
$controllerMethod = null;

foreach ($routes as $route => $handler) {
    if ($route === $request) {
        $controllerName = $handler['controller'];
        $controllerMethod = $handler['method'];
        break;
    }
}

// Manejar la ruta
if ($controllerName && $controllerMethod) {
    $controllerFile = 'controllers/' . $controllerName . '.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            if (method_exists($controller, $controllerMethod)) {
                // Manejar POST para /sismin/login
                if ($request === '/sismin/login' && $method === 'POST') {
                    error_log('Manejando POST para /sismin/login');
                    $controller->login();
                } else {
                    $controller->$controllerMethod();
                }
            } else {
                error_log('Método no encontrado: ' . $controllerMethod);
                header('HTTP/1.0 404 Not Found');
                echo 'Método no encontrado';
            }
        } else {
            error_log('Controlador no encontrado: ' . $controllerName);
            header('HTTP/1.0 404 Not Found');
            echo 'Controlador no encontrado';
        }
    } else {
        error_log('Archivo de controlador no encontrado: ' . $controllerFile);
        header('HTTP/1.0 404 Not Found');
        echo 'Controlador no encontrado';
    }
} else {
    error_log('Ruta no encontrada: ' . $request);
    header('HTTP/1.0 404 Not Found');
    echo 'Ruta no encontrada';
}