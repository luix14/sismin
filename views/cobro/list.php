<?php
// Evitar acceso directo
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
                    <h3 class="m-0 text-dark"><i class="fas fa-list mr-2"></i> Listado de Cobros</h3>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?php echo BASE_URL; ?>cobro" class="btn btn-sm btn-success" data-toggle="tooltip" title="Registrar nuevo cobro">
                        <i class="fas fa-plus mr-1"></i> Nuevo Cobro
                    </a>
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
                <?php } elseif (isset($error)) { ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
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
            <div class="card card-outline card-light mb-4 fade-in">
                <div class="card-header bg-light border-bottom">
                    <h4 class="card-title text-dark"><i class="fas fa-filter mr-2"></i> Filtros</h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="filterForm" method="GET" action="<?php echo BASE_URL; ?>cobro/list">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="folio">Folio</label>
                                    <input type="text" class="form-control" id="folio" name="folio" value="<?php echo htmlspecialchars($filters['folio'] ?? ''); ?>" placeholder="Ej. COBRO-000001">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="rfc">RFC</label>
                                    <input type="text" class="form-control" id="rfc" name="rfc" value="<?php echo htmlspecialchars($filters['rfc'] ?? ''); ?>" placeholder="Ej. SITJ0001019K9">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($filters['nombre'] ?? ''); ?>" placeholder="Ej. Juan Sierra">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_desde">Fecha Desde</label>
                                    <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="<?php echo htmlspecialchars($filters['fecha_desde'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_hasta">Fecha Hasta</label>
                                    <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="<?php echo htmlspecialchars($filters['fecha_hasta'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="button" class="btn btn-sm btn-secondary mr-2" id="clearFilters"><i class="fas fa-undo mr-1"></i> Limpiar</button>
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filtrar</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card card-outline card-light fade-in">
                <div class="card-header bg-light border-bottom">
                    <h4 class="card-title text-dark"><i class="fas fa-table mr-2"></i> Cobros Registrados</h4>
                </div>
                <div class="card-body">
                    <?php if ($error) { ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle mr-2"></i> No se pudieron cargar los cobros: <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php } else { ?>
                        <table class="table table-bordered table-hover">
                            <thead style="background: #e9ecef;">
                                <tr>
                                    <th style="width: 10%;">Folio</th>
                                    <th style="width: 15%;">RFC</th>
                                    <th style="width: 25%;">Nombre</th>
                                    <th style="width: 15%;">Total</th>
                                    <th style="width: 15%;">Fecha</th>
                                    <th style="width: 10%;">Estatus</th>
                                    <th style="width: 15%;">Usuario</th>
                                    <th style="width: 15%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($cobros)) { ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No se encontraron cobros.</td>
                                    </tr>
                                <?php } else { ?>
                                    <?php foreach ($cobros as $cobro) { ?>
                                        <tr class="fade-in">
                                            <td><?php echo htmlspecialchars($cobro['folio']); ?></td>
                                            <td><?php echo htmlspecialchars($cobro['rfc']); ?></td>
                                            <td><?php echo htmlspecialchars($cobro['nombre_contribuyente']); ?></td>
                                            <td class="text-right">$<?php echo number_format($cobro['total'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($cobro['fecha']); ?></td>
                                            <td><?php echo htmlspecialchars($cobro['estatus']); ?></td>
                                            <td><?php echo htmlspecialchars($cobro['usuario_nombre'] ?? 'Sistema'); ?></td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>cobro/receipt?id=<?php echo $cobro['id']; ?>&format=recibo" class="btn btn-sm btn-info" target="_blank" data-toggle="tooltip" title="Ver recibo">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                <?php if ($canEdit) { ?>
                                                    <a href="<?php echo BASE_URL; ?>cobro/edit?id=<?php echo $cobro['id']; ?>" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Editar cobro">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php } ?>
                                                <?php if ($canDelete) { ?>
                                                    <button type="button" class="btn btn-sm btn-danger delete-cobro" data-id="<?php echo $cobro['id']; ?>" data-toggle="tooltip" title="Eliminar cobro">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                        <!-- Paginación -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                Mostrando <?php echo count($cobros); ?> de <?php echo $totalRecords; ?> registros
                            </div>
                            <nav>
                                <ul class="pagination">
                                    <?php if ($page > 1) { ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&folio=<?php echo urlencode($filters['folio'] ?? ''); ?>&rfc=<?php echo urlencode($filters['rfc'] ?? ''); ?>&nombre=<?php echo urlencode($filters['nombre'] ?? ''); ?>&fecha_desde=<?php echo urlencode($filters['fecha_desde'] ?? ''); ?>&fecha_hasta=<?php echo urlencode($filters['fecha_hasta'] ?? ''); ?>">Anterior</a>
                                        </li>
                                    <?php } ?>
                                    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&folio=<?php echo urlencode($filters['folio'] ?? ''); ?>&rfc=<?php echo urlencode($filters['rfc'] ?? ''); ?>&nombre=<?php echo urlencode($filters['nombre'] ?? ''); ?>&fecha_desde=<?php echo urlencode($filters['fecha_desde'] ?? ''); ?>&fecha_hasta=<?php echo urlencode($filters['fecha_hasta'] ?? ''); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php } ?>
                                    <?php if ($page < $totalPages) { ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&folio=<?php echo urlencode($filters['folio'] ?? ''); ?>&rfc=<?php echo urlencode($filters['rfc'] ?? ''); ?>&nombre=<?php echo urlencode($filters['nombre'] ?? ''); ?>&fecha_desde=<?php echo urlencode($filters['fecha_desde'] ?? ''); ?>&fecha_hasta=<?php echo urlencode($filters['fecha_hasta'] ?? ''); ?>">Siguiente</a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </nav>
                        </div>
                    <?php } ?>
                </div>
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
        timeOut: 5000,
        escapeHtml: false
    };

    // Limpiar filtros
    $('#clearFilters').click(function() {
        $('#filterForm')[0].reset();
        window.location.href = '<?php echo BASE_URL; ?>cobro/list';
    });

    // Eliminar cobro
    $('.delete-cobro').click(function() {
        const id = $(this).data('id');
        if (confirm('¿Estás seguro de eliminar este cobro? Esta acción no se puede deshacer.')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>cobro/delete',
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success('Cobro eliminado exitosamente.');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(response.error || 'Error al eliminar el cobro.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al eliminar cobro:', xhr.status, xhr.statusText, error);
                    toastr.error('Error al eliminar el cobro: ' + (xhr.statusText || 'Desconocido'));
                }
            });
        }
    });
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
</style>