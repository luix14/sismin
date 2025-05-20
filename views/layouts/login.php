<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/sismin/');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - SISMIN</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>vendor/adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>vendor/adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        .login-page {
            background: linear-gradient(135deg, #0097a6 0%, #006f7b 100%); /* Degradado cian a cian oscuro */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-box {
            background: #e0f7fa; /* Cian claro */
            border: 2px solid #cd42a5; /* Borde magenta */
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
            padding: 20px;
        }
        .login-box:hover {
            transform: translateY(-5px);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-logo img {
            max-width: 150px; /* Tamaño del logo en escritorio */
            transition: transform 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .login-logo img:hover {
            transform: scale(1.05);
        }
        .login-card-body {
            background: #ffffff;
            border-radius: 8px;
            padding: 20px;
        }
        .login-box-msg {
            font-size: 1.2rem;
            color: #0097a6; /* Color principal cian */
            margin-bottom: 20px;
        }
        .input-group .form-control {
            border-radius: 5px 0 0 5px;
            background: #f1faff; /* Fondo claro para campos */
            border: 1px solid #0097a6; /* Borde cian */
            color: #333;
        }
        .input-group .form-control::placeholder {
            color: #888;
        }
        .input-group .input-group-text {
            background: #0097a6; /* Fondo cian para íconos */
            border: 1px solid #0097a6;
            border-radius: 0 5px 5px 0;
            color: #ffffff;
        }
        .btn-primary {
            background: #0097a6; /* Color principal cian */
            border: none;
            border-radius: 5px;
            padding: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: #cd42a5; /* Hover magenta */
            transform: scale(1.05);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
        }
        .alert-success {
            background: #0097a6; /* Éxito en cian */
            color: #ffffff;
            border: none;
            border-radius: 5px;
        }
        .alert-danger {
            background: #cd42a5; /* Error en magenta */
            color: #ffffff;
            border: none;
            border-radius: 5px;
        }
        .alert .close {
            color: #ffffff;
            opacity: 0.8;
        }
        .alert .close:hover {
            opacity: 1;
        }
        @media (max-width: 767.98px) {
            .login-box {
                padding: 15px;
                margin: 10px;
            }
            .login-logo img {
                max-width: 120px; /* Tamaño del logo en móviles */
            }
            .login-box-msg {
                font-size: 1rem;
            }
            .input-group .form-control {
                font-size: 0.9rem;
            }
            .btn-primary {
                padding: 8px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <a href="<?php echo BASE_URL; ?>">
            <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="SISMIN Logo">
        </a>
    </div>
    <div class="card">
        <div class="card-body login-card-body">
			<p class="login-logo"> SISMIN</p>
            <p class="login-box-msg">Inicia sesión para continuar</p>
            <?php if (isset($_GET['success'])) { ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php } elseif (isset($_GET['error'])) { ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php } ?>
            <form action="<?php echo BASE_URL; ?>login" method="POST">
                <div class="input-group mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Usuario" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="<?php echo BASE_URL; ?>vendor/adminlte/plugins/jquery/jquery.min.js"></script>
<script src="<?php echo BASE_URL; ?>vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>vendor/adminlte/dist/js/adminlte.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</body>
</html>