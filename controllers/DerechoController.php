<?php
require_once 'models/Derecho.php';
require_once 'models/Usuario.php';

class DerechoController {
    private $pdo;
    private $derecho;
    private $usuario;

    public function __construct() {
        global $pdo;
        if (!$pdo instanceof PDO) {
            error_log('Error en DerechoController: $pdo no está inicializado');
            throw new Exception('Error de configuración: No se pudo conectar a la base de datos');
        }
        $this->pdo = $pdo;
        $this->derecho = new Derecho($pdo);
        $this->usuario = new Usuario($pdo);
    }

    private function hasPermission($action) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        $permissions = $this->usuario->getUserPermissions($_SESSION['user_id']);
        $actionMap = [
            'view' => 'view_derechos',
            'create' => 'create_derechos',
            'edit' => 'edit_derechos',
            'delete' => 'delete_derechos'
        ];
        return in_array($actionMap[$action] ?? '', $permissions);
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en DerechoController::index');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        if (!$this->hasPermission('view')) {
            error_log('Usuario sin permisos para ver derechos');
            header('Location: ' . BASE_URL . 'dashboard?error=' . urlencode('No tienes permisos para ver derechos'));
            exit;
        }
        try {
            $categorias = $this->derecho->getAllCategorias();
            $derechos = $this->derecho->getAll();
            // Obtener valor de UMA directamente desde la tabla uma
            $stmt = $this->pdo->prepare('SELECT valor FROM uma ORDER BY fecha_inicio DESC LIMIT 1');
            $stmt->execute();
            $uma_valor = $stmt->fetch(PDO::FETCH_ASSOC);
            $uma_valor = $uma_valor && is_numeric($uma_valor['valor']) ? floatval($uma_valor['valor']) : 1;
            if ($uma_valor <= 0) {
                error_log('Valor de UMA inválido en DerechoController::index, usando valor por defecto: 1');
                $uma_valor = 1;
            }
            error_log('Valor de UMA obtenido: ' . $uma_valor);
        } catch (PDOException $e) {
            error_log('Error en DerechoController::index: ' . $e->getMessage());
            $categorias = [];
            $derechos = [];
            $uma_valor = 1; // Valor por defecto
        }
        $view = 'views/derechos/index.php';
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
            echo json_encode(['error' => 'No tienes permisos para crear derechos']);
            exit;
        }
        try {
            $data = [
                'id_categoria' => intval($_POST['id_categoria'] ?? 0),
                'uma_valor' => floatval($_POST['uma_valor'] ?? 0),
                'descripcion' => trim($_POST['descripcion'] ?? '')
            ];
            if ($data['id_categoria'] <= 0 || empty($data['descripcion']) || $data['uma_valor'] <= 0) {
                throw new Exception('Datos inválidos en el formulario');
            }
            $folio = $this->derecho->getNextFolio($data['id_categoria']);
            $folio_str = sprintf('%03d', $folio);
            $categoria_codigo = $this->derecho->getCategoriaCodigo($data['id_categoria']);
            if (!$categoria_codigo) {
                throw new Exception('Categoría no encontrada');
            }
            $data['codigo'] = $categoria_codigo . $folio_str;
            $data['folio'] = $folio_str;
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM derechos WHERE codigo = ?');
            $stmt->execute([$data['codigo']]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Código ya existe');
            }
            if ($this->derecho->create($data)) {
                error_log('Derecho creado exitosamente: ' . $data['codigo']);
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Derecho creado exitosamente']);
            } else {
                throw new Exception('Error al crear el derecho');
            }
        } catch (Exception $e) {
            error_log('Error en DerechoController::create: ' . $e->getMessage());
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
            echo json_encode(['error' => 'No tienes permisos para editar derechos']);
            exit;
        }
        try {
            $id = intval($_POST['id'] ?? 0);
            $data = [
                'id_categoria' => intval($_POST['id_categoria'] ?? 0),
                'folio' => trim($_POST['folio'] ?? sprintf('%03d', $this->derecho->getNextFolio(intval($_POST['id_categoria'] ?? 0)))),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'uma_valor' => floatval($_POST['uma_valor'] ?? 0)
            ];
            if ($id <= 0 || $data['id_categoria'] <= 0 || !preg_match('/^\d{3}$/', $data['folio']) || empty($data['descripcion']) || $data['uma_valor'] <= 0) {
                throw new Exception('Datos inválidos en el formulario');
            }
            $categoria_codigo = $this->derecho->getCategoriaCodigo($data['id_categoria']);
            if (!$categoria_codigo) {
                throw new Exception('Categoría no encontrada');
            }
            $data['codigo'] = $categoria_codigo . $data['folio'];
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM derechos WHERE codigo = ? AND id != ?');
            $stmt->execute([$data['codigo'], $id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Código ya existe');
            }
            if ($this->derecho->update($id, $data)) {
                error_log('Derecho actualizado exitosamente: id=' . $id);
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Derecho actualizado exitosamente']);
            } else {
                throw new Exception('Error al actualizar el derecho');
            }
        } catch (Exception $e) {
            error_log('Error en DerechoController::edit: ' . $e->getMessage());
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
            echo json_encode(['error' => 'No tienes permisos para eliminar derechos']);
            exit;
        }
        try {
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('ID inválido');
            }
            if ($this->derecho->delete($id)) {
                error_log('Derecho eliminado exitosamente: id=' . $id);
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Derecho eliminado exitosamente']);
            } else {
                throw new Exception('Error al eliminar el derecho');
            }
        } catch (Exception $e) {
            error_log('Error en DerechoController::delete: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    public function search() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en DerechoController::search');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
        try {
            $term = trim($_GET['term'] ?? '');
            error_log('Buscando derechos con term: ' . $term);
            $results = $this->derecho->search($term);
            $response = ['results' => []];
            foreach ($results as $row) {
                $response['results'][] = [
                    'id' => $row['id'],
                    'codigo' => $row['codigo'],
                    'descripcion' => $row['descripcion'],
                    'costo' => floatval($row['costo'])
                ];
            }
            error_log('Resultados derechos: ' . json_encode($response));
            header('Content-Type: application/json');
            echo json_encode($response);
        } catch (PDOException $e) {
            error_log('Error en DerechoController::search: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error al buscar derechos: ' . $e->getMessage()]);
        }
        exit;
    }
}