<?php
require_once 'models/Usuario.php';

class UsuarioController {
    private $pdo;
    private $usuario;

    public function __construct() {
        global $pdo;
        if (!$pdo instanceof PDO) {
            error_log('Error en UsuarioController: $pdo no está inicializado');
            throw new Exception('Error de configuración: No se pudo conectar a la base de datos');
        }
        $this->pdo = $pdo;
        $this->usuario = new Usuario($pdo);
    }

    private function hasPermission($action) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        $permissions = $this->usuario->getUserPermissions($_SESSION['user_id']);
        $actionMap = [
            'view' => 'view_usuarios',
            'edit' => 'edit_usuarios',
            'create' => 'create_usuarios'
        ];
        return in_array($actionMap[$action] ?? '', $permissions) || $this->isAdmin();
    }

    private function isAdmin() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        $stmt = $this->pdo->prepare('SELECT role_id, username FROM usuarios WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($user['role_id'] === 4 || strtolower($user['username']) === 'admin');
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en UsuarioController::index');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        if (!$this->hasPermission('view')) {
            error_log('Usuario sin permisos para ver usuarios');
            header('Location: ' . BASE_URL . 'dashboard?error=' . urlencode('No tienes permisos para ver usuarios'));
            exit;
        }
        try {
            // Obtener todos los usuarios
            $stmt = $this->pdo->query('
                SELECT u.id, u.username, r.nombre AS rol
                FROM usuarios u
                LEFT JOIN roles r ON u.role_id = r.id
                ORDER BY u.username
            ');
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log('UsuarioController::index - Usuarios cargados: ' . count($usuarios));
            $view = 'views/usuarios/index.php';
            require 'views/layouts/main.php';
        } catch (PDOException $e) {
            error_log('Error en UsuarioController::index: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'dashboard?error=' . urlencode('Error al cargar la lista de usuarios'));
            exit;
        }
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en UsuarioController::create');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        if (!$this->hasPermission('create')) {
            error_log('Usuario sin permisos para crear usuarios');
            header('Location: ' . BASE_URL . 'dashboard?error=' . urlencode('No tienes permisos para crear usuarios'));
            exit;
        }
        try {
            // Obtener todos los roles
            $stmt = $this->pdo->query('SELECT id, nombre FROM roles ORDER BY nombre');
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Obtener todos los permisos
            $stmt = $this->pdo->query('SELECT id, nombre, modulo, descripcion FROM permisos ORDER BY modulo, nombre');
            $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log('UsuarioController::create - Roles y permisos cargados');
            $view = 'views/usuarios/create.php';
            require 'views/layouts/main.php';
        } catch (PDOException $e) {
            error_log('Error en UsuarioController::create: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'usuarios?error=' . urlencode('Error al cargar el formulario de creación'));
            exit;
        }
    }

    public function store() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en UsuarioController::store');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        if (!$this->hasPermission('create')) {
            error_log('Usuario sin permisos para crear usuarios');
            header('Location: ' . BASE_URL . 'dashboard?error=' . urlencode('No tienes permisos para crear usuarios'));
            exit;
        }
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $role_id = isset($_POST['role_id']) ? intval($_POST['role_id']) : 0;
        $permisos = isset($_POST['permisos']) ? $_POST['permisos'] : [];

        // Validar datos
        if (empty($username) || empty($password) || $role_id <= 0) {
            header('Location: ' . BASE_URL . 'usuarios/create?error=' . urlencode('Todos los campos obligatorios deben completarse'));
            exit;
        }
        if (strlen($username) > 50 || strlen($password) > 255) {
            header('Location: ' . BASE_URL . 'usuarios/create?error=' . urlencode('El nombre de usuario o la contraseña exceden la longitud máxima permitida'));
            exit;
        }
        if (!preg_match('/^[a-zA-Z0-9_.]+$/', $username)) {
            header('Location: ' . BASE_URL . 'usuarios/create?error=' . urlencode('El nombre de usuario solo puede contener letras, números, puntos y guiones bajos'));
            exit;
        }

        try {
            // Verificar si el usuario ya existe
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE username = ?');
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                header('Location: ' . BASE_URL . 'usuarios/create?error=' . urlencode('El nombre de usuario ya está en uso'));
                exit;
            }

            // Hash de la contraseña
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Insertar nuevo usuario
            $stmt = $this->pdo->prepare('INSERT INTO usuarios (username, password, role_id) VALUES (?, ?, ?)');
            $stmt->execute([$username, $passwordHash, $role_id]);
            $user_id = $this->pdo->lastInsertId();

            // Insertar permisos personalizados
            foreach ($permisos as $permiso_id => $habilitado) {
                if ($habilitado == 1) {
                    $stmt = $this->pdo->prepare('INSERT INTO usuarios_permisos (usuario_id, permiso_id, habilitado) VALUES (?, ?, ?)');
                    $stmt->execute([$user_id, $permiso_id, 1]);
                }
            }

            error_log('UsuarioController::store - Usuario creado ID=' . $user_id . ', username=' . $username . ', role_id=' . $role_id);
            header('Location: ' . BASE_URL . 'usuarios?success=' . urlencode('Usuario creado correctamente'));
            exit;
        } catch (PDOException $e) {
            error_log('Error en UsuarioController::store: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'usuarios/create?error=' . urlencode('Error al crear el usuario'));
            exit;
        }
    }

    public function edit() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en UsuarioController::edit');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        if (!$this->hasPermission('edit')) {
            error_log('Usuario sin permisos para editar usuarios');
            header('Location: ' . BASE_URL . 'dashboard?error=' . urlencode('No tienes permisos para editar usuarios'));
            exit;
        }
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'usuarios?error=' . urlencode('ID de usuario inválido'));
            exit;
        }
        try {
            // Obtener datos del usuario
            $stmt = $this->pdo->prepare('SELECT id, username, role_id FROM usuarios WHERE id = ?');
            $stmt->execute([$id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$usuario) {
                header('Location: ' . BASE_URL . 'usuarios?error=' . urlencode('Usuario no encontrado'));
                exit;
            }
            // Obtener todos los roles
            $stmt = $this->pdo->query('SELECT id, nombre FROM roles ORDER BY nombre');
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Obtener todos los permisos
            $stmt = $this->pdo->query('SELECT id, nombre, modulo, descripcion FROM permisos ORDER BY modulo, nombre');
            $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Obtener permisos del usuario
            $stmt = $this->pdo->prepare('SELECT permiso_id, habilitado FROM usuarios_permisos WHERE usuario_id = ?');
            $stmt->execute([$id]);
            $usuarioPermisos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $usuarioPermisos[$row['permiso_id']] = $row['habilitado'];
            }
            error_log('UsuarioController::edit - Permisos cargados para usuario ID=' . $id . ': ' . json_encode($permisos));
            $view = 'views/usuarios/edit.php';
            require 'views/layouts/main.php';
        } catch (PDOException $e) {
            error_log('Error en UsuarioController::edit: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'usuarios?error=' . urlencode('Error al cargar el usuario'));
            exit;
        }
    }

    public function update() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en UsuarioController::update');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        if (!$this->hasPermission('edit')) {
            error_log('Usuario sin permisos para actualizar usuarios');
            header('Location: ' . BASE_URL . 'dashboard?error=' . urlencode('No tienes permisos para actualizar usuarios'));
            exit;
        }
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $role_id = isset($_POST['role_id']) ? intval($_POST['role_id']) : 0;
        $permisos = isset($_POST['permisos']) ? $_POST['permisos'] : [];
        if ($id <= 0 || $role_id <= 0) {
            header('Location: ' . BASE_URL . 'usuarios?error=' . urlencode('Datos inválidos'));
            exit;
        }
        try {
            // Actualizar rol del usuario
            $stmt = $this->pdo->prepare('UPDATE usuarios SET role_id = ? WHERE id = ?');
            $stmt->execute([$role_id, $id]);
            // Limpiar permisos existentes
            $stmt = $this->pdo->prepare('DELETE FROM usuarios_permisos WHERE usuario_id = ?');
            $stmt->execute([$id]);
            // Insertar nuevos permisos
            foreach ($permisos as $permiso_id => $habilitado) {
                if ($habilitado == 1) {
                    $stmt = $this->pdo->prepare('INSERT INTO usuarios_permisos (usuario_id, permiso_id, habilitado) VALUES (?, ?, ?)');
                    $stmt->execute([$id, $permiso_id, 1]);
                }
            }
            error_log('UsuarioController::update - Usuario ID=' . $id . ' actualizado con role_id=' . $role_id . ', permisos=' . json_encode($permisos));
            header('Location: ' . BASE_URL . 'usuarios?success=' . urlencode('Usuario actualizado correctamente'));
            exit;
        } catch (PDOException $e) {
            error_log('Error en UsuarioController::update: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'usuarios?error=' . urlencode('Error al actualizar el usuario'));
            exit;
        }
    }
}