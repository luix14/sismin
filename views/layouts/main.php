<?php
if (!defined('BASE_URL')) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}

// Inicializar conexión PDO
try {
    global $pdo;
    if (!isset($pdo)) {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=sismin_db;charset=utf8',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
} catch (PDOException $e) {
    error_log('Error al conectar a la base de datos en main.php: ' . $e->getMessage());
    die('Error de conexión a la base de datos');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISMIN - Sistema Municipal</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>vendor/adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>vendor/adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        body {
            background: #f4f6f9;
            margin: 0;
        }
        .wrapper {
            min-height: 100vh;
        }
        .main-footer {
            background: #e9ecef;
            padding: 10px;
            text-align: center;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Barra de Navegación -->
    <?php include 'views/layouts/topbar.php'; ?>
    <!-- Barra Lateral -->
    <?php include 'views/layouts/sidebar.php'; ?>
    <!-- Contenido -->
    <div class="content">
        <?php include $view; ?>
    </div>
    <!-- Pie de Página -->
    <footer class="main-footer">
        <strong>SISMIN © 2025</strong> Todos los derechos reservados.
    </footer>
</div>
<script src="<?php echo BASE_URL; ?>vendor/adminlte/plugins/jquery/jquery.min.js"></script>
<script src="<?php echo BASE_URL; ?>vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>vendor/adminlte/dist/js/adminlte.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(document).ready(function() {
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Configurar toastr
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3000
    };

    // Forzar visibilidad del topbar
    $('.main-header').css('display', 'block');

    // Colapsar sidebar por defecto en móviles
    if (window.innerWidth <= 767.98) {
        $('body').removeClass('sidebar-open').addClass('sidebar-collapse');
    }
});
</script>
</body>
</html>