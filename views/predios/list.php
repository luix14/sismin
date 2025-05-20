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
                    <h3 class="m-0 text-dark"><i class="fas fa-home mr-2"></i> Listado de Predios</h3>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-sm btn-info mr-2" data-toggle="tooltip" title="Ayuda" onclick="mostrarAyuda()">
                        <i class="fas fa-question-circle"></i> Ayuda
                    </button>
                </div>
            </div>
        </div>
    </section>
    <section class="content" style="padding-top: 60px;">
        <div class="container-fluid">
            <div id="alertContainer">
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
            </div>
            <!-- Formulario de Búsqueda -->
            <div class="card card-outline card-light mb-4 fade-in">
                <div class="card-header bg-light border-bottom">
                    <h4 class="card-title text-dark"><i class="fas fa-search mr-2"></i> Buscar Predios</h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="searchForm" method="GET" action="<?php echo BASE_URL; ?>predios/list">
                        <div class="input-group">
                            <input type="text" class="form-control" id="search" name="search" placeholder="Buscar por clave catastral, propietario o RFC" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary" data-toggle="tooltip" title="Buscar">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Listado de Predios -->
            <div class="card card-outline card-light fade-in">
                <div class="card-header bg-light border-bottom">
                    <h4 class="card-title text-dark"><i class="fas fa-table mr-2"></i> Predios Registrados</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover" id="prediosTable">
                        <thead style="background: #e9ecef;">
                            <tr>
                                <th style="width: 15%;">Clave Catastral</th>
                                <th style="width: 20%;">Propietario</th>
                                <th style="width: 15%;">RFC</th>
                                <th style="width: 10%;">Homoclave</th>
                                <th style="width: 15%;">Municipio</th>
                                <th style="width: 15%;">Ubicación</th>
                                <th style="width: 10%;">Superficie Terreno (m²)</th>
                                <th style="width: 10%;">Superficie Construcción (m²)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($predios)) { ?>
                                <tr>
                                    <td colspan="8" class="text-center">No se encontraron registros. Por favor, verifica los datos en la base de datos o importa nuevos datos en 'Importar Predios'.</td>
                                </tr>
                            <?php } else { ?>
                                <?php foreach ($predios as $predio) { ?>
                                    <tr class="fade-in">
                                        <td><?php echo htmlspecialchars($predio['clave_catastral']); ?></td>
                                        <td><?php echo htmlspecialchars($predio['propietario']); ?></td>
                                        <td><?php echo htmlspecialchars($predio['rfc']); ?></td>
                                        <td><?php echo htmlspecialchars($predio['homoclave']); ?></td>
                                        <td><?php echo htmlspecialchars($predio['municipio']); ?></td>
                                        <td><?php 
                                            $ubicacion = trim($predio['ubicacion_calle'] . ' ' . 
                                                        ($predio['ubicacion_numero_interior'] ? 'Int. ' . $predio['ubicacion_numero_interior'] : '') . ', ' . 
                                                        $predio['ubicacion_colonia']);
                                            echo htmlspecialchars($ubicacion);
                                        ?></td>
                                        <td><?php echo htmlspecialchars($predio['superficie_terreno']); ?></td>
                                        <td><?php echo htmlspecialchars($predio['superficie_construccion']); ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <!-- Paginación -->
                <?php if ($totalPages > 1) { ?>
                    <div class="card-footer">
                        <ul class="pagination pagination-sm m-0 float-right">
                            <!-- Primera página -->
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL; ?>predios/list?search=<?php echo urlencode($search); ?>&page=1" title="Primera página">«</a>
                            </li>
                            <!-- Página anterior -->
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL; ?>predios/list?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>" title="Página anterior"><</a>
                            </li>
                            <?php
                            // Mostrar primeras 4 páginas
                            $start = 1;
                            $end = min(4, $totalPages);
                            for ($i = $start; $i <= $end; $i++) { ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo BASE_URL; ?>predios/list?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php } ?>
                            <?php if ($totalPages > 4) { ?>
                                <!-- Puntos suspensivos si hay más de 4 páginas -->
                                <?php if ($end < $totalPages - 3) { ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php } ?>
                                <!-- Últimas 3 páginas -->
                                <?php
                                $start = max($end + 1, $totalPages - 2);
                                for ($i = $start; $i <= $totalPages; $i++) { ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>predios/list?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php } ?>
                            <?php } ?>
                            <!-- Página siguiente -->
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL; ?>predios/list?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>" title="Página siguiente">></a>
                            </li>
                            <!-- Última página -->
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL; ?>predios/list?search=<?php echo urlencode($search); ?>&page=<?php echo $totalPages; ?>" title="Última página">»</a>
                            </li>
                        </ul>
                    </div>
                <?php } ?>
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

    // Validar búsqueda
    $('#searchForm').on('submit', function(e) {
        const search = $('#search').val().trim();
        if (!search) {
            toastr.warning('Por favor, ingresa un término de búsqueda.');
            e.preventDefault();
            $('#search').addClass('is-invalid');
            return false;
        }
        $('#search').addClass('is-valid');
    });

    // Mostrar ayuda
    function mostrarAyuda() {
        toastr.info('1. Usa el campo de búsqueda para filtrar por clave catastral, propietario o RFC.<br>2. Navega entre páginas usando los controles de paginación.<br>3. Importa nuevos predios en la sección "Importar Predios".', 'Ayuda para Listado de Predios');
    }
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
.form-control.is-valid, .form-control.is-invalid {
    transition: border-color 0.3s;
}
.table-hover tbody tr:hover {
    background-color: #f1f3f5;
    transition: background-color 0.2s;
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
.input-group .form-control, select.form-control {
    height: calc(2.25rem + 2px);
    font-size: 1rem;
}
.input-group-text {
    background: #e9ecef;
}
.pagination .page-item.active .page-link {
    background-color: #28a745;
    border-color: #28a745;
}
.pagination .page-link {
    transition: all 0.3s;
}
.pagination .page-link:hover {
    background-color: #e9ecef;
}
</style>