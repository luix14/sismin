<?php
require_once 'models/Contribuyente.php';
require_once 'models/Usuario.php';

class ContribuyenteController {
    private $pdo;
    private $contribuyente;
    private $usuario;

    public function __construct() {
        global $pdo;
        if (!$pdo instanceof PDO) {
            error_log('Error en ContribuyenteController: $pdo no está inicializado');
            throw new Exception('Error de configuración: No se pudo conectar a la base de datos');
        }
        $this->pdo = $pdo;
        $this->contribuyente = new Contribuyente($pdo);
        $this->usuario = new Usuario($pdo);
    }

    private function hasPermission($action) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        $permissions = $this->usuario->getUserPermissions($_SESSION['user_id']);
        $actionMap = [
            'view' => 'view_contribuyentes',
            'create' => 'create_contribuyentes',
            'update' => 'update_contribuyentes'
        ];
        return in_array($actionMap[$action] ?? '', $permissions);
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en ContribuyenteController::index');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        if (!$this->hasPermission('view')) {
            error_log('Usuario sin permisos para ver contribuyentes');
            header('Location: ' . BASE_URL . 'dashboard?error=' . urlencode('No tienes permisos para ver contribuyentes'));
            exit;
        }
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 50;
        try {
            $data = $this->contribuyente->search($search, $page, $limit);
            $contribuyentes = $data['results'];
            $total = $data['total'];
            $totalPages = ceil($total / $limit);
            if ($page > $totalPages && $totalPages > 0) {
                $page = $totalPages;
                $data = $this->contribuyente->search($search, $page, $limit);
                $contribuyentes = $data['results'];
            }
            error_log('Listado de contribuyentes: search=' . $search . ', page=' . $page . ', total=' . $total . ', results=' . count($contribuyentes));
        } catch (PDOException $e) {
            error_log('Error en ContribuyenteController::index: ' . $e->getMessage());
            $contribuyentes = [];
            $total = 0;
            $totalPages = 0;
            $page = 1;
        }
        $view = 'views/contribuyentes/index.php';
        require 'views/layouts/main.php';
    }

    public function update() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en ContribuyenteController::update');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        if (!$this->hasPermission('update')) {
            error_log('Usuario sin permisos para actualizar contribuyentes');
            header('Location: ' . BASE_URL . 'contribuyentes?error=' . urlencode('No tienes permisos para actualizar contribuyentes'));
            exit;
        }
        try {
            $mensaje = $this->contribuyente->updateFromPredios();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $mensaje]);
            exit;
        } catch (Exception $e) {
            error_log('Error en ContribuyenteController::update: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en ContribuyenteController::create');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
        if (!$this->hasPermission('create')) {
            error_log('Usuario sin permisos para crear contribuyentes');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No tienes permisos para crear contribuyentes']);
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        try {
            $rfc = trim($_POST['rfc'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $direccion_fiscal = trim($_POST['direccion_fiscal'] ?? '');
            $regimen_fiscal = trim($_POST['regimen_fiscal'] ?? '');
            $codigo_postal = trim($_POST['codigo_postal'] ?? '');
            if (empty($rfc) || empty($nombre) || empty($direccion_fiscal)) {
                throw new Exception('Los campos RFC, Nombre y Dirección Fiscal son obligatorios');
            }
            $this->contribuyente->create($rfc, $nombre, $direccion_fiscal, $regimen_fiscal, $codigo_postal);
            error_log('Contribuyente creado exitosamente: RFC=' . $rfc);
            echo json_encode(['success' => true, 'rfc' => $rfc, 'nombre' => $nombre]);
            exit;
        } catch (Exception $e) {
            error_log('Error en ContribuyenteController::create: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    public function search() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en ContribuyenteController::search');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
        try {
            $term = trim($_GET['term'] ?? '');
            $results = $this->contribuyente->search($term, 1, 10);
            $results = array_map(function($c) {
                return [
                    'id' => $c['rfc'],
                    'text' => $c['rfc'] . ' - ' . $c['nombre'],
                    'rfc' => $c['rfc'],
                    'nombre' => $c['nombre'],
                    'direccion_fiscal' => $c['direccion_fiscal'],
                    'telefono' => '' // Teléfono no está en la tabla, se permite ingreso manual
                ];
            }, $results['results']);
            error_log('Búsqueda de contribuyentes: term=' . $term . ', results=' . count($results));
            header('Content-Type: application/json');
            echo json_encode(['results' => $results]);
            exit;
        } catch (PDOException $e) {
            error_log('Error en ContribuyenteController::search: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error al buscar contribuyentes: ' . $e->getMessage()]);
            exit;
        }
    }
}