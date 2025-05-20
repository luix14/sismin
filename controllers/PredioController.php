<?php
require_once 'models/Predio.php';
require_once 'models/Usuario.php';

class PredioController {
    private $pdo;
    private $predio;
    private $usuario;

    public function __construct() {
        global $pdo;
        if (!$pdo instanceof PDO) {
            error_log('Error en PredioController: $pdo no está inicializado');
            throw new Exception('Error de configuración: No se pudo conectar a la base de datos');
        }
        $this->pdo = $pdo;
        $this->predio = new Predio($pdo);
        $this->usuario = new Usuario($pdo);
    }

    private function hasPermission($action) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        $permissions = $this->usuario->getUserPermissions($_SESSION['user_id']);
        $actionMap = [
            'view' => 'view_predios',
            'import' => 'import_predios'
        ];
        return in_array($actionMap[$action] ?? '', $permissions);
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en PredioController::index');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        if (!$this->hasPermission('import')) {
            error_log('Usuario sin permisos para importar predios');
            header('Location: ' . BASE_URL . 'dashboard?error=' . urlencode('No tienes permisos para importar predios'));
            exit;
        }
        error_log('Usuario autenticado en PredioController::index, user_id=' . $_SESSION['user_id']);
        $view = 'views/predios/index.php';
        require 'views/layouts/main.php';
    }

    public function import() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en PredioController::import');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
        if (!$this->hasPermission('import')) {
            error_log('Usuario sin permisos para importar predios');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No tienes permisos para importar predios']);
            exit;
        }
        if ($_FILES['padron_file']['error'] !== UPLOAD_ERR_OK) {
            error_log('Error al cargar el archivo en PredioController::import');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error al cargar el archivo']);
            exit;
        }
        $file = $_FILES['padron_file']['tmp_name'];
        try {
            $total = 0;
            $exitosos = 0;
            $nuevos = 0;
            $actualizados = 0;
            $fallidos = 0;
            $errores = [];
            if (($handle = fopen($file, 'r')) !== false) {
                while (($line = fgets($handle)) !== false) {
                    $total++;
                    $fields = [
                        trim(substr($line, 0, 2)),
                        trim(substr($line, 2, 2)),
                        trim(substr($line, 4, 6)),
                        trim(substr($line, 10, 11)),
                        trim(substr($line, 21, 11)),
                        trim(substr($line, 32, 11)),
                        trim(substr($line, 43, 15)),
                        trim(substr($line, 58, 16)),
                        trim(substr($line, 74, 8)),
                        trim(substr($line, 82, 41)),
                        trim(substr($line, 123, 6)),
                        trim(substr($line, 129, 2)),
                        trim(substr($line, 131, 5)),
                        trim(substr($line, 136, 2)),
                        trim(substr($line, 138, 3)),
                        trim(substr($line, 141, 51)),
                        trim(substr($line, 192, 31)),
                        trim(substr($line, 223, 6)),
                        trim(substr($line, 229, 2)),
                        trim(substr($line, 231, 5)),
                        trim(substr($line, 236, 3)),
                        trim(substr($line, 239, 4)),
                        trim(substr($line, 243, 3)),
                        trim(substr($line, 246, 4)),
                        trim(substr($line, 250, 31)),
                        trim(substr($line, 281, 31)),
                        trim(substr($line, 312, 6)),
                        trim(substr($line, 318, 2)),
                        trim(substr($line, 320, 5)),
                        trim(substr($line, 325, 9)),
                        trim(substr($line, 334, 16)),
                        floatval(trim(substr($line, 350, 15))),
                        floatval(trim(substr($line, 365, 15))),
                        floatval(trim(substr($line, 380, 15))),
                        floatval(trim(substr($line, 395, 15))),
                        floatval(trim(substr($line, 410, 14))),
                        floatval(trim(substr($line, 424, 14))),
                        trim(substr($line, 438, 8)),
                        trim(substr($line, 457, 8)),
                        trim(substr($line, 466, 9)),
                        trim(substr($line, 475, 8)),
                        trim(substr($line, 483, 28))
                    ];
                    try {
                        $result = $this->predio->importOrUpdate($fields);
                        $exitosos++;
                        if ($result === 'inserted') {
                            $nuevos++;
                        } elseif ($result === 'updated') {
                            $actualizados++;
                        }
                    } catch (Exception $e) {
                        $fallidos++;
                        $clave = $fields[7];
                        $errores[] = "Fila $total (Clave: $clave): " . $e->getMessage();
                    }
                }
                fclose($handle);
                $mensaje = "Importación completada. Total: $total, Exitosos: $exitosos, Nuevos: $nuevos, Actualizados: $actualizados, Fallidos: $fallidos";
                if ($fallidos > 0) {
                    $mensaje .= ". Errores: " . implode('; ', array_slice($errores, 0, 5));
                }
                error_log('Importación de predios completada: ' . $mensaje);
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $mensaje]);
                exit;
            } else {
                error_log('Error al abrir el archivo en PredioController::import');
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Error al abrir el archivo']);
                exit;
            }
        } catch (Exception $e) {
            error_log('Error en PredioController::import: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    public function list() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en PredioController::list');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        if (!$this->hasPermission('view')) {
            error_log('Usuario sin permisos para ver predios');
            header('Location: ' . BASE_URL . 'dashboard?error=' . urlencode('No tienes permisos para ver predios'));
            exit;
        }
        error_log('Usuario autenticado en PredioController::list, user_id=' . ($_SESSION['user_id'] ?? 'none'));
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 50;
        try {
            $data = $this->predio->search($search, $page, $limit);
            $predios = $data['results'];
            $total = $data['total'];
            $totalPages = ceil($total / $limit);
            if ($page > $totalPages && $totalPages > 0) {
                $page = $totalPages;
                $data = $this->predio->search($search, $page, $limit);
                $predios = $data['results'];
            }
            error_log('Listado de predios: search=' . $search . ', page=' . $page . ', total=' . $total . ', results=' . count($predios));
        } catch (PDOException $e) {
            error_log('Error en PredioController::list: ' . $e->getMessage());
            $predios = [];
            $total = 0;
            $totalPages = 0;
            $page = 1;
        }
        $view = 'views/predios/list.php';
        require 'views/layouts/main.php';
    }
}