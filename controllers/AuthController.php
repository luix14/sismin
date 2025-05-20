<?php
class AuthController {
    private $pdo;

    public function __construct() {
        global $pdo;
        if (!$pdo instanceof PDO) {
            error_log('Error en AuthController: $pdo no está inicializado');
            throw new Exception('Error de configuración: No se pudo conectar a la base de datos');
        }
        $this->pdo = $pdo;
    }

    public function index() {
        if (isset($_SESSION['user_id'])) {
            error_log('Usuario ya autenticado, redirigiendo a dashboard');
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
        $view = 'views/auth/login.php';
        require 'views/layouts/login.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log('Método no permitido en AuthController::login, redirigiendo a login');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            error_log('Faltan credenciales en AuthController::login');
            $error = 'Por favor, ingresa usuario y contraseña';
            $view = 'views/auth/login.php';
            require 'views/layouts/login.php';
            return;
        }

        try {
            error_log("Intentando login para usuario: $username");
            $stmt = $this->pdo->prepare('SELECT id, username, password, role_id FROM usuarios WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                error_log("Usuario no encontrado: $username");
                $error = 'Usuario o contraseña incorrectos';
                $view = 'views/auth/login.php';
                require 'views/layouts/login.php';
                return;
            }

            if (!password_verify($password, $user['password'])) {
                error_log("Contraseña incorrecta para usuario: $username");
                $error = 'Usuario o contraseña incorrectos';
                $view = 'views/auth/login.php';
                require 'views/layouts/login.php';
                return;
            }

            // Inicio de sesión exitoso
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id'];
            error_log("Inicio de sesión exitoso para usuario: $username, redirigiendo a dashboard");
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        } catch (PDOException $e) {
            error_log('Error en AuthController::login: ' . $e->getMessage());
            $error = 'Error al iniciar sesión: ' . $e->getMessage();
            $view = 'views/auth/login.php';
            require 'views/layouts/login.php';
        } catch (Exception $e) {
            error_log('Error general en AuthController::login: ' . $e->getMessage());
            $error = 'Error inesperado al iniciar sesión';
            $view = 'views/auth/login.php';
            require 'views/layouts/login.php';
        }
    }

    public function logout() {
        // Limpiar todas las variables de sesión
        $_SESSION = [];
        // Destruir la sesión
        session_destroy();
        // Eliminar la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        error_log('Sesión cerrada, redirigiendo a login');
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
}