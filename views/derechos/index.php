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
                    <h3 class="m-0 text-dark"><i class="fas fa-list mr-2"></i> Gestión de Derechos</h3>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-sm btn-info mr-2" data-toggle="tooltip" title="Ayuda" onclick="mostrarAyuda()">
                        <i class="fas fa-question-circle"></i> Ayuda
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary mr-2" id="resetForm" data-toggle="tooltip" title="Limpiar formulario">
                        <i class="fas fa-undo"></i> Limpiar
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="submitForm" data-toggle="tooltip" title="Crear derecho">
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
            <form id="derechoForm" method="POST" action="<?php echo BASE_URL; ?>derechos/create">
                <div class="card card-outline card-light mb-4 fade-in">
                    <div class="card-header bg-light border-bottom">
                        <h4 class="card-title text-dark"><i class="fas fa-plus mr-2"></i> Nuevo Derecho</h4>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="id_categoria">Categoría <span class="text-danger">*</span></label>
                                    <select class="form-control" id="id_categoria" name="id_categoria" required>
                                        <option value="">Selecciona una categoría</option>
                                        <?php foreach ($categorias as $categoria) { ?>
                                            <option value="<?php echo $categoria['id']; ?>" data-codigo="<?php echo htmlspecialchars($categoria['codigo']); ?>">
                                                <?php echo htmlspecialchars($categoria['nombre'] . ' (' . $categoria['codigo'] . ')'); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="uma_valor">Valor en UMA <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="uma_valor" name="uma_valor" step="0.01" min="0" placeholder="Ej. 1.5" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="costo">Costo Calculado (MXN)</label>
                                    <input type="text" class="form-control" id="costo" readonly value="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="descripcion">Descripción <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Descripción del derecho" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <!-- Listado de Derechos -->
            <div class="card card-outline card-light fade-in">
                <div class="card-header bg-light border-bottom">
                    <h4 class="card-title text-dark"><i class="fas fa-table mr-2"></i> Derechos Registrados</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover" id="derechosTable">
                        <thead style="background: #e9ecef;">
                            <tr>
                                <th style="width: 10%;">ID</th>
                                <th style="width: 15%;">Código</th>
                                <th style="width: 20%;">Categoría</th>
                                <th style="width: 25%;">Descripción</th>
                                <th style="width: 10%;">Valor en UMA</th>
                                <th style="width: 10%;">Costo (MXN)</th>
                                <th style="width: 10%;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($derechos)) { ?>
                                <tr>
                                    <td colspan="7" class="text-center">No se encontraron derechos.</td>
                                </tr>
                            <?php } else { ?>
                                <?php foreach ($derechos as $derecho) { ?>
                                    <tr class="fade-in">
                                        <td><?php echo htmlspecialchars($derecho['id']); ?></td>
                                        <td><?php echo htmlspecialchars($derecho['codigo']); ?></td>
                                        <td><?php echo htmlspecialchars($derecho['categoria']); ?></td>
                                        <td><?php echo htmlspecialchars($derecho['descripcion']); ?></td>
                                        <td><?php echo number_format($derecho['uma_valor'], 2); ?></td>
                                        <td><?php echo number_format($derecho['costo'], 2); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary edit-derecho" 
                                                    data-id="<?php echo $derecho['id']; ?>" 
                                                    data-id-categoria="<?php echo $derecho['id_categoria']; ?>" 
                                                    data-folio="<?php echo htmlspecialchars($derecho['folio']); ?>" 
                                                    data-descripcion="<?php echo htmlspecialchars($derecho['descripcion']); ?>" 
                                                    data-uma-valor="<?php echo $derecho['uma_valor']; ?>" 
                                                    data-toggle="tooltip" title="Editar derecho">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-derecho" 
                                                    data-id="<?php echo $derecho['id']; ?>" 
                                                    data-toggle="tooltip" title="Eliminar derecho">
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
                    <button type="button" class="btn btn-success btn-sm" id="submitFloating" data-toggle="tooltip" title="Crear derecho">
                        <i class="fas fa-save mr-1"></i> Crear
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

    // Valor de UMA (obtenido del servidor)
    const umaValorRaw = <?php echo json_encode($uma_valor); ?>;
    // Parsear el valor de UMA como número, usar 108.57 como valor por defecto si no es válido
    const umaValor = (typeof umaValorRaw === 'number' && !isNaN(umaValorRaw) && umaValorRaw > 0) 
        ? parseFloat(umaValorRaw) 
        : 108.57;
    console.log('Valor de UMA procesado:', umaValor);

    // Mostrar advertencia si el valor de UMA no es válido
    if (umaValor === 108.57) {
        toastr.warning('No se encontró un valor de UMA válido en la base de datos. Usando valor por defecto (108.57).');
    }

    // Calcular costo basado en UMA
    function calcularCosto() {
        const umaValorInput = parseFloat($('#uma_valor').val()) || 0;
        const costo = umaValorInput * umaValor;
        $('#costo').val(isNaN(costo) ? '0.00' : costo.toFixed(2));
    }

    // Actualizar costo al cambiar UMA
    $('#uma_valor').on('input change', calcularCosto);

    // Limpiar formulario
    $('#resetForm').click(function() {
        $('#derechoForm')[0].reset();
        $('#derechoForm').attr('action', '<?php echo BASE_URL; ?>derechos/create');
        $('#derechoForm input[name="id"]').remove();
        $('#submitForm, #submitFloating').text('Crear').removeClass('btn-primary').addClass('btn-success');
        $('#costo').val('0.00');
        $('.form-control').removeClass('is-valid is-invalid');
        toastr.info('Formulario limpiado.');
    });

    // Editar derecho
    $('.edit-derecho').click(function() {
        const id = $(this).data('id');
        const idCategoria = $(this).data('id-categoria');
        const folio = $(this).data('folio');
        const descripcion = $(this).data('descripcion');
        const umaValorInput = $(this).data('uma-valor');
        $('#derechoForm').attr('action', '<?php echo BASE_URL; ?>derechos/edit');
        $('#derechoForm').prepend(`<input type="hidden" name="id" value="${id}">`);
        $('#id_categoria').val(idCategoria);
        $('#descripcion').val(descripcion);
        $('#uma_valor').val(umaValorInput);
        calcularCosto();
        $('#submitForm, #submitFloating').text('Guardar Cambios').removeClass('btn-success').addClass('btn-primary');
        toastr.info('Editando derecho.');
        $('html, body').animate({ scrollTop: 0 }, 300);
    });

    // Eliminar derecho
    $('.delete-derecho').click(function() {
        const id = $(this).data('id');
        if (confirm('¿Estás seguro de eliminar este derecho? Esta acción no se puede deshacer.')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>derechos/delete',
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success('Derecho eliminado exitosamente.');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(response.error || 'Error al eliminar el derecho.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al eliminar derecho:', xhr.status, xhr.statusText, error);
                    toastr.error('Error al eliminar el derecho: ' + (xhr.statusText || 'Desconocido'));
                }
            });
        }
    });

    // Envío del formulario
    function submitForm(e) {
        e.preventDefault();
        const idCategoria = $('#id_categoria').val();
        const descripcion = $('#descripcion').val().trim();
        const umaValorInput = parseFloat($('#uma_valor').val()) || 0;
        if (!idCategoria) {
            toastr.error('Por favor, selecciona una categoría.');
            $('#id_categoria').addClass('is-invalid');
            return;
        }
        if (!descripcion) {
            toastr.error('La descripción es obligatoria.');
            $('#descripcion').addClass('is-invalid');
            return;
        }
        if (umaValorInput <= 0) {
            toastr.error('El valor en UMA debe ser mayor a 0.');
            $('#uma_valor').addClass('is-invalid');
            return;
        }
        $('#id_categoria, #descripcion, #uma_valor').addClass('is-valid');

        const formData = new FormData(document.getElementById('derechoForm'));
        const action = $('#derechoForm').attr('action');

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
    $('#derechoForm').on('submit', function(e) {
        e.preventDefault();
        submitForm(e);
    });

    // Mostrar ayuda
    function mostrarAyuda() {
        toastr.info('1. Selecciona una categoría.<br>2. Ingresa la descripción y el valor en UMA.<br>3. El costo se calcula automáticamente.<br>4. Usa los botones para crear, editar o eliminar derechos.', 'Ayuda para Gestión de Derechos');
    }

    // Restaurar formulario al crear nuevo derecho
    function resetForm() {
        $('#derechoForm')[0].reset();
        $('#derechoForm').attr('action', '<?php echo BASE_URL; ?>derechos/create');
        $('#derechoForm input[name="id"]').remove();
        $('#submitForm, #submitFloating').text('Crear').removeClass('btn-primary').addClass('btn-success');
        $('#costo').val('0.00');
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