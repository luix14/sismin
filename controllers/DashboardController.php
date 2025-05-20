<?php
require_once 'models/Predio.php';
require_once 'models/Contribuyente.php';
require_once 'models/Cobro.php';
require_once 'models/Categoria.php';
require_once 'models/Usuario.php';

class DashboardController {
    private $pdo;
    private $predio;
    private $contribuyente;
    private $cobro;
    private $categoria;
    private $usuario;

    public function __construct() {
        global $pdo;
        if (!$pdo instanceof PDO) {
            error_log('Error en DashboardController: $pdo no está inicializado');
            throw new Exception('Error de configuración: No se pudo conectar a la base de datos');
        }
        $this->pdo = $pdo;
        $this->predio = new Predio($pdo);
        $this->contribuyente = new Contribuyente($pdo);
        $this->cobro = new Cobro($pdo);
        $this->categoria = new Categoria($pdo);
        $this->usuario = new Usuario($pdo);
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en DashboardController::index');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        error_log('Usuario autenticado en DashboardController::index, user_id=' . $_SESSION['user_id']);
        $view = 'views/dashboard/index.php';
        require 'views/layouts/main.php';
    }

    public function stats() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en DashboardController::stats');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
        try {
            $prediosStmt = $this->pdo->query('SELECT COUNT(*) FROM predios');
            $contribuyentesStmt = $this->pdo->query('SELECT COUNT(*) FROM contribuyentes');
            $cobrosStmt = $this->pdo->query('SELECT COUNT(*) FROM cobros WHERE fecha >= CURDATE() - INTERVAL 30 DAY');
            $categoriasStmt = $this->pdo->query('SELECT COUNT(*) FROM categorias');
            
            $response = [
                'predios' => $prediosStmt->fetchColumn(),
                'contribuyentes' => $contribuyentesStmt->fetchColumn(),
                'cobros' => $cobrosStmt->fetchColumn(),
                'categorias' => $categoriasStmt->fetchColumn()
            ];
            
            error_log('Estadísticas cargadas: ' . json_encode($response));
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } catch (PDOException $e) {
            error_log('Error en DashboardController::stats: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error al cargar estadísticas']);
            exit;
        }
    }

    public function prediosPorMunicipio() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en DashboardController::prediosPorMunicipio');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
        try {
            $stmt = $this->pdo->query('SELECT municipio, COUNT(*) as count FROM predios GROUP BY municipio ORDER BY count DESC LIMIT 5');
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $labels = array_column($results, 'municipio');
            $data = array_column($results, 'count');
            
            $response = [
                'labels' => $labels,
                'data' => $data
            ];
            
            error_log('Datos de predios por municipio cargados: ' . json_encode($response));
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } catch (PDOException $e) {
            error_log('Error en DashboardController::prediosPorMunicipio: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error al cargar datos del gráfico']);
            exit;
        }
    }
}