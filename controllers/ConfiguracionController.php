<?php
require_once 'models/UMA.php';
require_once 'models/DatosMunicipio.php';

class ConfiguracionController {
    private $pdo;
    private $uma;
    private $datosMunicipio;
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->uma = new UMA($pdo);
        $this->datosMunicipio = new DatosMunicipio($pdo);
    }
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en index de Configuraciones');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        $view = 'views/configuraciones/index.php';
        require 'views/layouts/main.php';
    }
    public function municipio() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en municipio de Configuraciones');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        $config = $this->datosMunicipio->getLatest();
        $view = 'views/configuraciones/municipio.php';
        require 'views/layouts/main.php';
    }
    public function updateMunicipio() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en updateMunicipio de Configuraciones');
            header('Location: ' . BASE_URL . 'login');
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
            if (empty($nombre) || empty($direccion)) {
                throw new Exception('Nombre y Dirección del municipio son obligatorios');
            }
            $this->datosMunicipio->update($nombre, $direccion, $telefono, $correo, $rfc, $horario_atencion);
            header('Location: ' . BASE_URL . 'configuraciones/municipio?success=' . urlencode('Datos actualizados correctamente'));
            exit;
        } catch (Exception $e) {
            header('Location: ' . BASE_URL . 'configuraciones/municipio?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
    public function uma() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en uma de Configuraciones');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        $uma = $this->uma->getLatest();
        $view = 'views/configuraciones/uma.php';
        require 'views/layouts/main.php';
    }
    public function updateUMA() {
        if (!isset($_SESSION['user_id'])) {
            error_log('Usuario no autenticado en updateUMA de Configuraciones');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'configuraciones/uma?error=' . urlencode('Método no permitido'));
            exit;
        }
        try {
            $valor = floatval($_POST['valor'] ?? 0);
            $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            if ($valor <= 0 || empty($fecha_inicio)) {
                throw new Exception('El valor de UMA y la fecha de inicio son obligatorios');
            }
            $this->uma->update($valor, $fecha_inicio, $descripcion);
            header('Location: ' . BASE_URL . 'configuraciones/uma?success=' . urlencode('UMA actualizada correctamente'));
            exit;
        } catch (Exception $e) {
            header('Location: ' . BASE_URL . 'configuraciones/uma?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}