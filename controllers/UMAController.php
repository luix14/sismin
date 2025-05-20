<?php
require_once 'models/UMA.php';
require_once 'models/Usuario.php';

class UMAController {
    private $pdo;
    private $uma;
    private $usuario;

    public function __construct() {
        global $pdo;
        if (!$pdo instanceof PDO) {
            error_log('Error en UMAController: $pdo no está inicializado');
            throw new Exception('Error de configuración: No se pudo conectar a la base de datos');
        }
        $this->pdo = $pdo;
        $this->uma = new UMA($pdo);
        $this->usuario = new Usuario($pdo);
    }

    private function hasPermission($action) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        $permissions = $this->usuario->getUserPermissions($_SESSION['user_id']);
        $actionMap = [
            'view' => 'view_uma',
            'create' => 'create_uma',
            'edit' => 'edit_uma',
            'delete' => 'delete_uma'
        ];
        return in_array($actionMap[$action] ?? '', $permissions);
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en UMAController::index');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        if (!$this->hasPermission('view')) {
            error_log('Usuario sin permisos para ver UMA');
            header('Location: ' . BASE_URL . 'dashboard?error=' . urlencode('No tienes permisos para ver UMA'));
            exit;
        }
        try {
            $umas = $this->uma->getAll();
        } catch (PDOException $e) {
            error_log('Error en UMAController::index: ' . $e->getMessage());
            $umas = [];
        }
        $view = 'views/configuraciones/uma.php';
        require 'views/layouts/main.php';
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
        if (!$this->hasPermission('create')) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No tienes permisos para crear UMA']);
            exit;
        }
        try {
            $valor = floatval($_POST['valor'] ?? 0);
            $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            if ($valor <= 0) {
                throw new Exception('El valor de UMA debe ser mayor a 0');
            }
            if (empty($fecha_inicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio)) {
                throw new Exception('La fecha de inicio es obligatoria y debe tener el formato AAAA-MM-DD');
            }
            $this->uma->create($valor, $fecha_inicio, $descripcion);
            error_log('UMA creada exitosamente: valor=' . $valor);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'UMA creada exitosamente']);
        } catch (Exception $e) {
            error_log('Error en UMAController::create: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    public function edit() {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
        if (!$this->hasPermission('edit')) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No tienes permisos para editar UMA']);
            exit;
        }
        try {
            $id = intval($_POST['id'] ?? 0);
            $valor = floatval($_POST['valor'] ?? 0);
            $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            if ($id <= 0) {
                throw new Exception('ID de UMA inválido');
            }
            if ($valor <= 0) {
                throw new Exception('El valor de UMA debe ser mayor a 0');
            }
            if (empty($fecha_inicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio)) {
                throw new Exception('La fecha de inicio es obligatoria y debe tener el formato AAAA-MM-DD');
            }
            $this->uma->edit($id, $valor, $fecha_inicio, $descripcion);
            error_log('UMA actualizada exitosamente: id=' . $id);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'UMA actualizada exitosamente']);
        } catch (Exception $e) {
            error_log('Error en UMAController::edit: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    public function delete() {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
        if (!$this->hasPermission('delete')) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No tienes permisos para eliminar UMA']);
            exit;
        }
        try {
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('ID de UMA inválido');
            }
            $this->uma->delete($id);
            error_log('UMA eliminada exitosamente: id=' . $id);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'UMA eliminada exitosamente']);
        } catch (Exception $e) {
            error_log('Error en UMAController::delete: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}