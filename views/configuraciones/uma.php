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
                    <h3 class="m-0 text-dark"><i class="fas fa-money-bill-alt mr-2"></i> Gestión de UMA</h3>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-sm btn-info mr-2" data-toggle="tooltip" title="Ayuda" onclick="mostrarAyuda()">
                        <i class="fas fa-question-circle"></i> Ayuda
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary mr-2" id="resetForm" data-toggle="tooltip" title="Limpiar formulario">
                        <i class="fas fa-undo"></i> Limpiar
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="submitForm" data-toggle="tooltip" title="Crear UMA">
                        <i class="fas fa-save"></i> Crear
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
            <!-- Formulario de Creación -->
            <form id="umaForm" method="POST" action="<?php echo BASE_URL; ?>uma/create">
                <div class="card card-outline card-light mb-4 fade-in">
                    <div class="card-header bg-light border-bottom">
                        <h4 class="card-title text-dark"><i class="fas fa-plus mr-2"></i> Nuevo Valor de UMA</h4>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="valor">Valor de UMA (MXN) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="valor" name="valor" step="0.01" min="0" placeholder="Ej. 108.57" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Notas o descripción (opcional)"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <!-- Listado de Valores de UMA -->
            <div class="card card-outline card-light fade-in">
                <div class="card-header bg-light border-bottom">
                    <h4 class="card-title text-dark"><i class="fas fa-table mr-2"></i> Valores de UMA Registrados</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover" id="umaTable">
                        <thead style="background: #e9ecef;">
                            <tr>
                                <th style="width: 10%;">ID</th>
                                <th style="width: 20%;">Valor (MXN)</th>
                                <th style="width: 20%;">Fecha de Inicio</th>
                                <th style="width: 40%;">Descripción</th>
                                <th style="width: 10%;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($umas)) { ?>
                                <tr>
                                    <td colspan="5" class="text-center">No se encontraron valores de UMA.</td>
                                </tr>
                            <?php } else { ?>
                                <?php foreach ($umas as $uma) { ?>
                                    <tr class="fade-in">
                                        <td><?php echo htmlspecialchars($uma['id']); ?></td>
                                        <td><?php echo number_format($uma['valor'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($uma['fecha_inicio']); ?></td>
                                        <td><?php echo htmlspecialchars($uma['descripcion'] ?? ''); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary edit-uma" 
                                                    data-id="<?php echo $uma['id']; ?>" 
                                                    data-valor="<?php echo $uma['valor']; ?>" 
                                                    data-fecha-inicio="<?php echo htmlspecialchars($uma['fecha_inicio']); ?>" 
                                                    data-descripcion="<?php echo htmlspecialchars($uma['descripcion'] ?? ''); ?>" 
                                                    data-toggle="tooltip" title="Editar UMA">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-uma" 
                                                    data-id="<?php echo $uma['id']; ?>" 
                                                    data-toggle="tooltip" title="Eliminar UMA">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Barra de Acción Flotante -->
            <div style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
                <div class="d-flex align-items-center bg-white border rounded p-2 shadow">
                    <button type="button" class="btn btn-success btn-sm" id="submitFloating" data-toggle="tooltip" title="Crear UMA">
                        <iчным классом 'fas fa-save mr-1'></i> Crear
                    </button>
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
        timeOut: 3000
    };

    // Validación de fecha
    const fechaRegex = /^\d{4}-\d{2}-\d{2}$/;

    // Limpiar formulario
    $('#resetForm').click(function() {
        $('#umaForm')[0].reset();
        $('#umaForm').attr('action', '<?php echo BASE_URL; ?>uma/create');
        $('#umaForm input[name="id"]').remove();
        $('#submitForm, #submitFloating').text('Crear').removeClass('btn-primary').addClass('btn-success');
        $('.form-control').removeClass('is-valid is-invalid');
        toastr.info('Formulario limpiado.');
    });

    // Editar UMA
    $('.edit-uma').click(function() {
        const id = $(this).data('id');
        const valor = $(this).data('valor');
        const fechaInicio = $(this).data('fecha-inicio');
        const descripcion = $(this).data('descripcion');
        $('#umaForm').attr('action', '<?php echo BASE_URL; ?>uma/edit');
        $('#umaForm').prepend(`<input type="hidden" name="id" value="${id}">`);
        $('#valor').val(valor);
        $('#fecha_inicio').val(fechaInicio);
        $('#descripcion').val(descripcion);
        $('#submitForm, #submitFloating').text('Guardar Cambios').removeClass('btn-success').addClass('btn-primary');
        toastr.info('Editando valor de UMA.');
        $('html, body').animate({ scrollTop: 0 }, 300);
    });

    // Eliminar UMA
    $('.delete-uma').click(function() {
        const id = $(this).data('id');
        if (confirm('¿Estás seguro de eliminar este valor de UMA? Esta acción no se puede deshacer.')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>uma/delete',
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success('Valor de UMA eliminado exitosamente.');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(response.error || 'Error al eliminar el valor de UMA.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al eliminar UMA:', xhr.status, xhr.statusText, error);
                    toastr.error('Error al eliminar el valor de UMA: ' + (xhr.statusText || 'Desconocido'));
                }
            });
        }
    });

    // Envío del formulario
    function submitForm(e) {
        e.preventDefault();
        const valor = parseFloat($('#valor').val()) || 0;
        const fechaInicio = $('#fecha_inicio').val().trim();
        if (valor <= 0) {
            toastr.error('El valor de UMA debe ser mayor a 0.');
            $('#valor').addClass('is-invalid');
            return;
        }
        if (!fechaInicio || !fechaRegex.test(fechaInicio)) {
            toastr.error('La fecha de inicio es obligatoria y debe tener el formato AAAA-MM-DD.');
            $('#fecha_inicio').addClass('is-invalid');
            return;
        }
        $('#valor, #fecha_inicio').addClass('is-valid');

        const formData = new FormData(document.getElementById('umaForm'));
        const action = $('#umaForm').attr('action');

        toastr.info('Procesando...');
        $.ajax({
            url: action,
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Operación exitosa.');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.error || 'Error al procesar la solicitud.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud:', xhr.status, xhr.statusText, error);
                toastr.error('Error al procesar la solicitud: ' + (xhr.statusText || 'Desconocido'));
            }
        });
    }

    // Vincular envío a botones
    $('#submitForm, #submitFloating').click(function(e) {
        submitForm(e);
    });

    // Prevenir envío tradicional del formulario
    $('#umaForm').on('submit', function(e) {
        e.preventDefault();
        submitForm(e);
    });

    // Mostrar ayuda
    function mostrarAyuda() {
        toastr.info('1. Ingresa el valor de UMA en MXN.<br>2. Selecciona la fecha de inicio.<br>3. Añade una descripción (opcional).<br>4. Usa los botones para crear, editar o eliminar valores de UMA.', 'Ayuda para Gestión de UMA');
    }

    // Restaurar formulario al crear nuevo UMA
    function resetForm() {
        $('#umaForm')[0].reset();
        $('#umaForm').attr('action', '<?php echo BASE_URL; ?>uma/create');
        $('#umaForm input[name="id"]').remove();
        $('#submitForm, #submitFloating').text('Crear').removeClass('btn-primary').addClass('btn-success');
        $('.form-control').removeClass('is-valid is-invalid');
    }

    // Restaurar tras éxito
    <?php if (isset($_GET['success'])) { ?>
        resetForm();
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
</style>