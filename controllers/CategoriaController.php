<?php
require_once 'models/Categoria.php';
require_once 'models/Usuario.php';

class CategoriaController {
    private $pdo;
    private $categoria;
    private $usuario;

    public function __construct() {
        global $pdo;
        if (!$pdo instanceof PDO) {
            error_log('Error en CategoriaController: $pdo no está inicializado');
            throw new Exception('Error de configuración: No se pudo conectar a la base de datos');
        }
        $this->pdo = $pdo;
        $this->categoria = new Categoria($pdo);
        $this->usuario = new Usuario($pdo);
    }

    private function hasPermission($action) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        $permissions = $this->usuario->getUserPermissions($_SESSION['user_id']);
        $actionMap = [
            'view' => 'view_categorias',
            'create' => 'create_categorias',
            'edit' => 'edit_categorias',
            'delete' => 'delete_categorias'
        ];
        return in_array($actionMap[$action] ?? '', $permissions);
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en CategoriaController::index');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        if (!$this->hasPermission('view')) {
            error_log('Usuario sin permisos para ver categorías');
            header('Location: ' . BASE_URL . 'dashboard?error=' . urlencode('No tienes permisos para ver categorías'));
            exit;
        }
        try {
            $categorias = $this->categoria->getAll();
        } catch (PDOException $e) {
            error_log('Error en CategoriaController::index: ' . $e->getMessage());
            $categorias = [];
        }
        $view = 'views/categorias/index.php';
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
            echo json_encode(['error' => 'No tienes permisos para crear categorías']);
            exit;
        }
        try {
            $data = [
                'codigo' => strtoupper(trim($_POST['codigo'] ?? '')),
                'nombre' => trim($_POST['nombre'] ?? '')
            ];
            if (empty($data['codigo']) || empty($data['nombre'])) {
                throw new Exception('Código y nombre son obligatorios');
            }
            if (!preg_match('/^[A-Z]{2,3}$/', $data['codigo'])) {
                throw new Exception('Código debe ser 2 o 3 letras mayúsculas');
            }
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM categorias WHERE codigo = ?');
            $stmt->execute([$data['codigo']]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Código ya existe');
            }
            if ($this->categoria->create($data)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Categoría creada exitosamente']);
            } else {
                throw new Exception('Error al crear la categoría');
            }
        } catch (Exception $e) {
            error_log('Error en CategoriaController::create: ' . $e->getMessage());
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
            echo json_encode(['error' => 'No tienes permisos para editar categorías']);
            exit;
        }
        try {
            $id = intval($_POST['id'] ?? 0);
            $data = [
                'codigo' => strtoupper(trim($_POST['codigo'] ?? '')),
                'nombre' => trim($_POST['nombre'] ?? '')
            ];
            if ($id <= 0) {
                throw new Exception('ID de categoría inválido');
            }
            if (empty($data['codigo']) || empty($data['nombre'])) {
                throw new Exception('Código y nombre son obligatorios');
            }
            if (!preg_match('/^[A-Z]{2,3}$/', $data['codigo'])) {
                throw new Exception('Código debe ser 2 o 3 letras mayúsculas');
            }
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM categorias WHERE codigo = ? AND id != ?');
            $stmt->execute([$data['codigo'], $id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Código ya existe');
            }
            if ($this->categoria->update($id, $data)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Categoría actualizada exitosamente']);
            } else {
                throw new Exception('Error al actualizar la categoría');
            }
        } catch (Exception $e) {
            error_log('Error en CategoriaController::edit: ' . $e->getMessage());
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
            echo json_encode(['error' => 'No tienes permisos para eliminar categorías']);
            exit;
        }
        try {
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('ID de categoría inválido');
            }
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM derechos WHERE id_categoria = ?');
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Categoría en uso, no se puede eliminar');
            }
            if ($this->categoria->delete($id)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Categoría eliminada exitosamente']);
            } else {
                throw new Exception('Error al eliminar la categoría');
            }
        } catch (Exception $e) {
            error_log('Error en CategoriaController::delete: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}