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
                    <h3 class="m-0 text-dark"><i class="fas fa-user-tie mr-2"></i> Listado de Contribuyentes</h3>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-sm btn-info mr-2" data-toggle="tooltip" title="Ayuda" onclick="mostrarAyuda()">
                        <i class="fas fa-question-circle"></i> Ayuda
                    </button>
                    <button type="button" class="btn btn-sm btn-primary mr-2" id="updateContribuyentes" data-toggle="tooltip" title="Actualizar desde predios">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="addContribuyente" data-toggle="modal" data-target="#contribuyenteModal" title="Añadir nuevo contribuyente">
                        <i class="fas fa-plus"></i> Añadir
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
                    <h4 class="card-title text-dark"><i class="fas fa-search mr-2"></i> Buscar Contribuyentes</h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="searchForm" method="GET" action="<?php echo BASE_URL; ?>contribuyentes">
                        <div class="input-group">
                            <input type="text" class="form-control" id="search" name="search" placeholder="Buscar por RFC o Nombre" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary" data-toggle="tooltip" title="Buscar">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Listado de Contribuyentes -->
            <div class="card card-outline card-light fade-in">
                <div class="card-header bg-light border-bottom">
                    <h4 class="card-title text-dark"><i class="fas fa-table mr-2"></i> Contribuyentes Registrados</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover" id="contribuyentesTable">
                        <thead style="background: #e9ecef;">
                            <tr>
                                <th style="width: 15%;">RFC</th>
                                <th style="width: 25%;">Nombre</th>
                                <th style="width: 30%;">Dirección Fiscal</th>
                                <th style="width: 15%;">Régimen Fiscal</th>
                                <th style="width: 15%;">Código Postal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($contribuyentes)) { ?>
                                <tr>
                                    <td colspan="5" class="text-center">No se encontraron registros. Por favor, haz clic en 'Actualizar Contribuyentes' o añade un nuevo contribuyente.</td>
                                </tr>
                            <?php } else { ?>
                                <?php foreach ($contribuyentes as $contribuyente) { ?>
                                    <tr class="fade-in">
                                        <td><?php echo htmlspecialchars($contribuyente['rfc']); ?></td>
                                        <td><?php echo htmlspecialchars($contribuyente['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($contribuyente['direccion_fiscal']); ?></td>
                                        <td><?php echo htmlspecialchars($contribuyente['regimen_fiscal'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($contribuyente['codigo_postal'] ?? ''); ?></td>
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
                                <a class="page-link" href="<?php echo BASE_URL; ?>contribuyentes?search=<?php echo urlencode($search); ?>&page=1" title="Primera página">&laquo;</a>
                            </li>
                            <!-- Página anterior -->
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL; ?>contribuyentes?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>" title="Página anterior">&lt;</a>
                            </li>
                            <?php
                            // Mostrar primeras 4 páginas
                            $start = 1;
                            $end = min(4, $totalPages);
                            for ($i = $start; $i <= $end; $i++) { ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo BASE_URL; ?>contribuyentes?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
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
                                        <a class="page-link" href="<?php echo BASE_URL; ?>contribuyentes?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php } ?>
                            <?php } ?>
                            <!-- Página siguiente -->
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL; ?>contribuyentes?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>" title="Página siguiente">&gt;</a>
                            </li>
                            <!-- Última página -->
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL; ?>contribuyentes?search=<?php echo urlencode($search); ?>&page=<?php echo $totalPages; ?>" title="Última página">&raquo;</a>
                            </li>
                        </ul>
                    </div>
                <?php } ?>
            </div>
            <!-- Barra de Acción Flotante -->
            <div style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
                <div class="d-flex align-items-center bg-white border rounded p-2 shadow">
                    <button type="button" class="btn btn-success btn-sm" id="addContribuyenteFloating" data-toggle="modal" data-target="#contribuyenteModal" title="Añadir nuevo contribuyente">
                        <i class="fas fa-plus mr-1"></i> Añadir
                    </button>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal para Añadir Contribuyente -->
<div class="modal fade" id="contribuyenteModal" tabindex="-1" role="dialog" aria-labelledby="contribuyenteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="contribuyenteModalLabel"><i class="fas fa-user-plus mr-2"></i> Añadir Nuevo Contribuyente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="contribuyenteForm" method="POST">
                <div class="modal-body">
                    <div id="modalAlertContainer"></div>
                    <div class="form-group">
                        <label for="rfc">RFC <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="rfc" name="rfc" maxlength="13" placeholder="Ej. XAXX010101000" required>
                    </div>
                    <div class="form-group">
                        <label for="nombre">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" maxlength="255" placeholder="Nombre completo" required>
                    </div>
                    <div class="form-group">
                        <label for="direccion_fiscal">Dirección Fiscal <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="direccion_fiscal" name="direccion_fiscal" rows="3" placeholder="Calle, número, colonia, etc." required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="regimen_fiscal">Régimen Fiscal</label>
                        <input type="text" class="form-control" id="regimen_fiscal" name="regimen_fiscal" maxlength="255" placeholder="Ej. Persona Física">
                    </div>
                    <div class="form-group">
                        <label for="codigo_postal">Código Postal</label>
                        <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" maxlength="5" placeholder="Ej. 12345">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="saveContribuyente">Guardar</button>
                </div>
            </form>
        </div>
    </div>
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

    // Actualizar contribuyentes
    $('#updateContribuyentes').click(function() {
        toastr.info('Actualizando contribuyentes...');
        $.ajax({
            url: '<?php echo BASE_URL; ?>contribuyentes/update',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.error || 'Error al actualizar contribuyentes.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al actualizar contribuyentes:', xhr.status, xhr.statusText, error);
                toastr.error('Error al actualizar contribuyentes: ' + (xhr.statusText || 'Desconocido'));
            }
        });
    });

    // Limpiar formulario del modal
    function resetForm() {
        $('#contribuyenteForm')[0].reset();
        $('.form-control').removeClass('is-valid is-invalid');
        $('#modalAlertContainer').empty();
    }

    // Abrir modal
    $('#addContribuyente, #addContribuyenteFloating').click(function() {
        resetForm();
        $('#contribuyenteModal').modal('show');
    });

    // Enviar formulario de creación
    $('#contribuyenteForm').on('submit', function(e) {
        e.preventDefault();
        const rfc = $('#rfc').val().trim();
        const nombre = $('#nombre').val().trim();
        const direccionFiscal = $('#direccion_fiscal').val().trim();
        if (!rfc) {
            toastr.error('El RFC es obligatorio.');
            $('#rfc').addClass('is-invalid');
            return;
        }
        if (!nombre) {
            toastr.error('El nombre es obligatorio.');
            $('#nombre').addClass('is-invalid');
            return;
        }
        if (!direccionFiscal) {
            toastr.error('La dirección fiscal es obligatoria.');
            $('#direccion_fiscal').addClass('is-invalid');
            return;
        }
        $('#rfc, #nombre, #direccion_fiscal').addClass('is-valid');

        const formData = new FormData(this);
        toastr.info('Guardando contribuyente...');
        $.ajax({
            url: '<?php echo BASE_URL; ?>contribuyentes/create',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success('Contribuyente creado exitosamente.');
                    $('#contribuyenteModal').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.error || 'Error al crear el contribuyente.');
                    $('#modalAlertContainer').html(
                        `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle mr-2"></i> ${response.error}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>`
                    );
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al crear contribuyente:', xhr.status, xhr.statusText, error);
                toastr.error('Error al crear el contribuyente: ' + (xhr.statusText || 'Desconocido'));
            }
        });
    });

    // Mostrar ayuda
    function mostrarAyuda() {
        toastr.info('1. Usa el campo de búsqueda para filtrar por RFC o nombre.<br>2. Haz clic en "Actualizar Contribuyentes" para sincronizar desde predios.<br>3. Usa "Añadir Contribuyente" para crear un nuevo registro.', 'Ayuda para Gestión de Contribuyentes');
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