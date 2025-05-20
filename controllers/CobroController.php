<?php
require_once 'models/Cobro.php';
require_once 'models/Usuario.php';

class CobroController {
    private $cobro;
    private $usuario;

    public function __construct() {
        global $pdo;
        $this->cobro = new Cobro($pdo);
        $this->usuario = new Usuario($pdo);
    }

    private function hasPermission($action) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        $permissions = $this->usuario->getUserPermissions($_SESSION['user_id']);
        
        $actionMap = [
            'view' => 'view_cobros',
            'edit' => 'edit_cobros',
            'delete' => 'delete_cobros',
            'cobrar' => 'cobrar'
        ];
        
        return in_array($actionMap[$action] ?? '', $permissions);
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en CobroController::index');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        $view = 'views/cobro/index.php';
        require 'views/layouts/main.php';
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en CobroController::create');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
        if (!$this->hasPermission('cobrar')) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No tienes permisos para registrar cobros']);
            exit;
        }

        try {
            $rfc = trim($_POST['rfc'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $observaciones = trim($_POST['observaciones'] ?? '');
            $derechos = json_decode($_POST['derechos'] ?? '[]', true);

            if (empty($rfc) || empty($nombre) || empty($direccion) || empty($derechos)) {
                throw new Exception('Datos incompletos');
            }

            $cobro_id = $this->cobro->create($rfc, $nombre, $telefono, $direccion, $observaciones, $derechos);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'cobro_id' => $cobro_id]);
            exit;
        } catch (Exception $e) {
            error_log('Error en CobroController::create: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    public function receipt() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en CobroController::receipt');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        try {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $format = isset($_GET['format']) ? $_GET['format'] : 'recibo';
            if ($id <= 0) {
                throw new Exception('ID de cobro inválido');
            }
            $this->cobro->generateReceipt($id, $format);
        } catch (Exception $e) {
            error_log('Error en CobroController::receipt: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    public function list() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en CobroController::list');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $error = null;
        $cobros = [];
        $totalRecords = 0;
        $totalPages = 0;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $filters = [
            'folio' => trim($_GET['folio'] ?? ''),
            'rfc' => trim($_GET['rfc'] ?? ''),
            'nombre' => trim($_GET['nombre'] ?? ''),
            'fecha_desde' => trim($_GET['fecha_desde'] ?? ''),
            'fecha_hasta' => trim($_GET['fecha_hasta'] ?? '')
        ];

        try {
            $result = $this->cobro->getAll($filters, $limit, $offset);
            $cobros = $result['cobros'];
            $totalRecords = $result['totalRecords'];
            $totalPages = ceil($totalRecords / $limit);
        } catch (Exception $e) {
            error_log('Error en CobroController::list: ' . $e->getMessage());
            $error = $e->getMessage();
        }

        $canEdit = $this->hasPermission('edit');
        $canDelete = $this->hasPermission('delete');

        $view = 'views/cobro/list.php';
        require 'views/layouts/main.php';
    }

    public function edit() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en CobroController::edit');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        if (!$this->hasPermission('edit')) {
            error_log('Usuario sin permisos para editar en CobroController::edit');
            header('Location: ' . BASE_URL . 'cobro/list?error=' . urlencode('No tienes permisos para editar cobros'));
            exit;
        }

        try {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id <= 0) {
                throw new Exception('ID de cobro inválido');
            }
            $cobro = $this->cobro->getById($id);
            $view = 'views/cobro/edit.php';
            require 'views/layouts/main.php';
        } catch (Exception $e) {
            error_log('Error en CobroController::edit: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'cobro/list?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function update() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en CobroController::update');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
        if (!$this->hasPermission('edit')) {
            error_log('Usuario sin permisos para actualizar en CobroController::update');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No tienes permisos para editar cobros']);
            exit;
        }

        try {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $rfc = trim($_POST['rfc'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $observaciones = trim($_POST['observaciones'] ?? '');
            $derechos = json_decode($_POST['derechos'] ?? '[]', true);

            if ($id <= 0 || empty($rfc) || empty($nombre) || empty($direccion) || empty($derechos)) {
                throw new Exception('Datos incompletos');
            }

            $this->cobro->update($id, $rfc, $nombre, $telefono, $direccion, $observaciones, $derechos);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            error_log('Error en CobroController::update: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    public function delete() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en CobroController::delete');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
        if (!$this->hasPermission('delete')) {
            error_log('Usuario sin permisos para eliminar en CobroController::delete');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No tienes permisos para eliminar cobros']);
            exit;
        }

        try {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if ($id <= 0) {
                throw new Exception('ID de cobro inválido');
            }
            $this->cobro->delete($id);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            error_log('Error en CobroController::delete: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
}