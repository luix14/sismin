<?php
if (!defined('BASE_URL')) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}
?>
<div class="content-wrapper" style="background: #f4f6f9;">
    <section class="content-header sticky-top bg-light border-bottom" style="padding: 10px 0; z-index: 1000;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="m-0 text-dark"><i class="fas fa-city mr-2"></i> Datos Generales del Municipio</h3>
                </div>
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>configuraciones">Configuración</a></li>
                        <li class="breadcrumb-item active">Municipio</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content" style="padding-top: 20px;">
        <div class="container-fluid">
            <div class="card card-outline card-light fade-in">
                <div class="card-header bg-light border-bottom">
                    <h4 class="card-title text-dark"><i class="fas fa-cog mr-2"></i> Configuración del Municipio</h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <form method="POST" action="<?php echo BASE_URL; ?>configuraciones/municipio/update" id="municipioForm">
                    <div class="card-body">
                        <?php if (isset($_GET['success'])) { ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                        <?php } elseif (isset($_GET['error'])) { ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="municipio_nombre">Nombre del Municipio <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="municipio_nombre" name="municipio_nombre" value="<?php echo htmlspecialchars($config['nombre'] ?? ''); ?>" maxlength="255" placeholder="Nombre del municipio" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="municipio_direccion">Dirección <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="municipio_direccion" name="municipio_direccion" value="<?php echo htmlspecialchars($config['direccion'] ?? ''); ?>" maxlength="255" placeholder="Dirección completa" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="municipio_telefono">Teléfono</label>
                                    <input type="text" class="form-control" id="municipio_telefono" name="municipio_telefono" value="<?php echo htmlspecialchars($config['telefono'] ?? ''); ?>" maxlength="20" placeholder="Ej. 1234567890">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="municipio_correo">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="municipio_correo" name="municipio_correo" value="<?php echo htmlspecialchars($config['correo'] ?? ''); ?>" maxlength="100" placeholder="Ej. contacto@municipio.com">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="municipio_rfc">RFC</label>
                                    <input type="text" class="form-control" id="municipio_rfc" name="municipio_rfc" value="<?php echo htmlspecialchars($config['rfc'] ?? ''); ?>" maxlength="13" placeholder="Ej. MJS140116AE8">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="municipio_horario">Horario de Atención</label>
                                    <input type="text" class="form-control" id="municipio_horario" name="municipio_horario" value="<?php echo htmlspecialchars($config['horario_atencion'] ?? ''); ?>" maxlength="100" placeholder="Ej. Lunes a Viernes, 8:00-15:30">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="municipio_eslogan">Slogan</label>
                                    <input type="text" class="form-control" id="municipio_eslogan" name="municipio_eslogan" value="<?php echo htmlspecialchars($config['eslogan'] ?? ''); ?>" maxlength="255" placeholder="Ej. Corazón de la Sierra">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script src="<?php echo BASE_URL; ?>vendor/adminlte/plugins/jquery/jquery.min.js"></script>
<script src="<?php echo BASE_URL; ?>vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

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

    // Validación del formulario
    $('#municipioForm').on('submit', function(e) {
        var telefono = $('#municipio_telefono').val();
        var correo = $('#municipio_correo').val();
        var rfc = $('#municipio_rfc').val();
        var eslogan = $('#municipio_eslogan').val();
        var direccion = $('#municipio_direccion').val();
        var horario = $('#municipio_horario').val();

        if (telefono && !/^[0-9]{10}$/.test(telefono)) {
            e.preventDefault();
            toastr.error('El teléfono debe tener 10 dígitos');
            return;
        }
        if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
            e.preventDefault();
            toastr.error('El correo electrónico no es válido');
            return;
        }
        if (rfc && !/^[A-Z0-9]{13}$/.test(rfc)) {
            e.preventDefault();
            toastr.error('El RFC debe tener 13 caracteres alfanuméricos');
            return;
        }
        if (eslogan && !/^[a-zA-Z0-9\s,.!?-]+$/.test(eslogan)) {
            e.preventDefault();
            toastr.error('El eslogan solo puede contener letras, números, espacios y puntuación básica');
            return;
        }
        if (direccion.length > 255) {
            e.preventDefault();
            toastr.error('La dirección no puede exceder 255 caracteres');
            return;
        }
        if (correo.length > 100) {
            e.preventDefault();
            toastr.error('El correo electrónico no puede exceder 100 caracteres');
            return;
        }
        if (horario.length > 100) {
            e.preventDefault();
            toastr.error('El horario de atención no puede exceder 100 caracteres');
            return;
        }
        if (eslogan.length > 255) {
            e.preventDefault();
            toastr.error('El eslogan no puede exceder 255 caracteres');
            return;
        }
    });

    <?php if (isset($_GET['error'])) { ?>
        toastr.error('<?php echo htmlspecialchars($_GET['error']); ?>');
    <?php } elseif (isset($_GET['success'])) { ?>
        toastr.success('<?php echo htmlspecialchars($_GET['success']); ?>');
    <?php } ?>
});
</script>

<style>
.card-outline.card-light {
    border: 1px solid #dee2e6;
    background: #ffffff;
    transition: transform 0.3s, box-shadow 0.3s;
}
.card-outline:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.1);
}
.card-header.bg-light {
    background: #e9ecef;
}
.btn:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: all 0.3s;
}
.fade-in {
    animation: fadeIn 0.5s;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.alert-dismissible .close {
    padding: 0.5rem;
}
</style>