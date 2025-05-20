<?php
if (!defined('BASE_URL')) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Usuario';
?>
<div class="content-wrapper" style="background: #f4f6f9;">
    <section class="content-header sticky-top bg-light border-bottom" style="padding: 10px 0; z-index: 1000;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-sm btn-info mr-2" data-toggle="tooltip" title="Ayuda" onclick="mostrarAyuda()">
                        <i class="fas fa-question-circle"></i> Ayuda
                    </button>
                </div>
            </div>
        </div>
    </section>
    <section class="content" style="padding-top: 20px;">
        <div class="container-fluid">
            <!-- Saludo Personalizado -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info alert-dismissible fade show fade-in" role="alert">
                        <h4 class="alert-heading">¡Bienvenido, <?php echo $username; ?>!</h4>
                        <p>Explora y gestiona los recursos de SISMIN desde este panel.</p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Estadísticas -->
            <div class="row fade-in">
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-home"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Predios Registrados</span>
                            <span class="info-box-number" id="prediosCount">Cargando...</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-user-tie"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Contribuyentes</span>
                            <span class="info-box-number" id="contribuyentesCount">Cargando...</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-money-bill"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Cobros Recientes</span>
                            <span class="info-box-number" id="cobrosCount">Cargando...</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger"><i class="fas fa-tags"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Categorías</span>
                            <span class="info-box-number" id="categoriasCount">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Accesos Rápidos -->
            <div class="row fade-in">
                <?php if (in_array('view_predios', $this->usuario->getUserPermissions($_SESSION['user_id']))) { ?>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>Predios</h3>
                                <p>Importar o listar predios</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <a href="<?php echo BASE_URL; ?>predios" class="small-box-footer">
                                Ir a Predios <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                <?php } ?>
                <?php if (in_array('view_contribuyentes', $this->usuario->getUserPermissions($_SESSION['user_id']))) { ?>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>Contribuyentes</h3>
                                <p>Gestionar contribuyentes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <a href="<?php echo BASE_URL; ?>contribuyentes" class="small-box-footer">
                                Ir a Contribuyentes <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                <?php } ?>
                <?php if (in_array('view_cobros', $this->usuario->getUserPermissions($_SESSION['user_id']))) { ?>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>Cobros</h3>
                                <p>Registrar o listar cobros</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-money-bill"></i>
                            </div>
                            <a href="<?php echo BASE_URL; ?>cobro" class="small-box-footer">
                                Ir a Cobros <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                <?php } ?>
                <?php if (in_array('view_configuraciones', $this->usuario->getUserPermissions($_SESSION['user_id']))) { ?>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>Configuraciones</h3>
                                <p>Ajustar UMA y municipio</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <a href="<?php echo BASE_URL; ?>configuraciones" class="small-box-footer">
                                Ir a Configuraciones <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <!-- Gráfico de Predios por Municipio -->
            <div class="row fade-in">
                <div class="col-12">
                    <div class="card card-outline card-light">
                        <div class="card-header bg-light border-bottom">
                            <h4 class="card-title text-dark"><i class="fas fa-chart-bar mr-2"></i> Predios por Municipio</h4>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="prediosChart" style="height: 250px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="<?php echo BASE_URL; ?>vendor/adminlte/plugins/jquery/jquery.min.js"></script>
<script src="<?php echo BASE_URL; ?>vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
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

    // Cargar estadísticas (simuladas, ajustar según API o datos reales)
    $.ajax({
        url: '<?php echo BASE_URL; ?>dashboard/stats',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#prediosCount').text(response.predios || 0);
            $('#contribuyentesCount').text(response.contribuyentes || 0);
            $('#cobrosCount').text(response.cobros || 0);
            $('#categoriasCount').text(response.categorias || 0);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar estadísticas:', xhr.status, xhr.statusText, error);
            toastr.error('Error al cargar estadísticas.');
        }
    });

    // Cargar datos para el gráfico
    $.ajax({
        url: '<?php echo BASE_URL; ?>dashboard/prediosPorMunicipio',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            const ctx = document.getElementById('prediosChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: response.labels || ['Municipio 1', 'Municipio 2', 'Municipio 3'],
                    datasets: [{
                        label: 'Predios por Municipio',
                        data: response.data || [0, 0, 0],
                        backgroundColor: ['#28a745', '#17a2b8', '#ffc107'],
                        borderColor: ['#28a745', '#17a2b8', '#ffc107'],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Número de Predios'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Municipio'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar datos del gráfico:', xhr.status, xhr.statusText, error);
            toastr.error('Error al cargar el gráfico de predios.');
        }
    });

    // Mostrar ayuda
    function mostrarAyuda() {
        toastr.info('Este es tu panel principal. Aquí puedes:<br>1. Ver estadísticas de predios, contribuyentes y cobros.<br>2. Acceder rápidamente a los módulos principales.<br>3. Visualizar la distribución de predios por municipio.', 'Ayuda para el Dashboard');
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
.info-box, .small-box {
    transition: transform 0.3s, box-shadow 0.3s;
}
.info-box:hover, .small-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.1);
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