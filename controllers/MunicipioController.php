<?php
require_once 'models/DatosMunicipio.php';

class MunicipioController {
    private $pdo;
    private $datosMunicipio;

    public function __construct() {
        global $pdo;
        if (!$pdo instanceof PDO) {
            error_log('Error en MunicipioController: $pdo no está inicializado');
            throw new Exception('Error de configuración: No se pudo conectar a la base de datos');
        }
        $this->pdo = $pdo;
        $this->datosMunicipio = new DatosMunicipio($pdo);
    }

    private function hasPermission($action) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        require_once 'models/Usuario.php';
        $usuario = new Usuario($this->pdo);
        $permissions = $usuario->getUserPermissions($_SESSION['user_id']);
        $actionMap = [
            'view' => 'view_configuraciones',
            'edit' => 'edit_configuraciones'
        ];
        $stmt = $this->pdo->prepare('SELECT role_id, username FROM usuarios WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $isAdmin = ($user['role_id'] === 4 || strtolower($user['username']) === 'admin');
        return in_array($actionMap[$action] ?? '', $permissions) || $isAdmin;
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en MunicipioController::index');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        if (!$this->hasPermission('view')) {
            error_log('Usuario sin permisos para ver datos del municipio');
            header('Location: ' . BASE_URL . 'dashboard?error=' . urlencode('No tienes permisos para ver los datos del municipio'));
            exit;
        }
        $config = $this->datosMunicipio->getLatest();
        $view = 'views/configuraciones/municipio.php';
        require 'views/layouts/main.php';
    }

    public function update() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en MunicipioController::update');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        if (!$this->hasPermission('edit')) {
            error_log('Usuario sin permisos para actualizar datos del municipio');
            header('Location: ' . BASE_URL . 'dashboard?error=' . urlencode('No tienes permisos para actualizar los datos del municipio'));
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'configuraciones/municipio?error=' . urlencode('Método no permitido'));
            exit;
        }
        try {
            $nombre = trim($_POST['municipio_nombre'] ?? '');
            $direccion = trim($_POST['municipio_direccion'] ?? '');
            $telefono = trim($_POST['municipio_telefono'] ?? '');
            $correo = trim($_POST['municipio_correo'] ?? '');
            $rfc = trim($_POST['municipio_rfc'] ?? '');
            $horario_atencion = trim($_POST['municipio_horario'] ?? '');
            $eslogan = trim($_POST['municipio_eslogan'] ?? '');
            if (empty($nombre) || empty($direccion)) {
                throw new Exception('Nombre y Dirección del municipio son obligatorios');
            }
            if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('El correo electrónico no es válido');
            }
            if (!empty($rfc) && !preg_match('/^[A-Z0-9]{13}$/', $rfc)) {
                throw new Exception('El RFC debe tener 13 caracteres alfanuméricos');
            }
            if (!empty($telefono) && !preg_match('/^[0-9]{10}$/', $telefono)) {
                throw new Exception('El teléfono debe tener 10 dígitos');
            }
            if (!empty($eslogan) && !preg_match('/^[a-zA-Z0-9\s,.!?-]+$/', $eslogan)) {
                throw new Exception('El eslogan solo puede contener letras, números, espacios y puntuación básica');
            }
            if (strlen($direccion) > 255) {
                throw new Exception('La dirección no puede exceder 255 caracteres');
            }
            if (strlen($correo) > 100) {
                throw new Exception('El correo electrónico no puede exceder 100 caracteres');
            }
            if (strlen($horario_atencion) > 100) {
                throw new Exception('El horario de atención no puede exceder 100 caracteres');
            }
            if (strlen($eslogan) > 255) {
                throw new Exception('El eslogan no puede exceder 255 caracteres');
            }
            $this->datosMunicipio->update($nombre, $direccion, $telefono, $correo, $rfc, $horario_atencion, $eslogan);
            error_log('MunicipioController::update - Datos del municipio actualizados: ' . $nombre . ', eslogan: ' . $eslogan);
            header('Location: ' . BASE_URL . 'configuraciones/municipio?success=' . urlencode('Datos actualizados correctamente'));
            exit;
        } catch (Exception $e) {
            error_log('Error en MunicipioController::update: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'configuraciones/municipio?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}