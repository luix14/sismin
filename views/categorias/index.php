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
                    <h3 class="m-0 text-dark"><i class="fas fa-list mr-2"></i> Gestión de Categorías</h3>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-sm btn-info mr-2" data-toggle="tooltip" title="Ayuda" onclick="mostrarAyuda()">
                        <i class="fas fa-question-circle"></i> Ayuda
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary mr-2" id="resetForm" data-toggle="tooltip" title="Limpiar formulario">
                        <i class="fas fa-undo"></i> Limpiar
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="submitForm" data-toggle="tooltip" title="Crear categoría">
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
            <form id="categoriaForm" method="POST" action="<?php echo BASE_URL; ?>categorias/create">
                <div class="card card-outline card-light mb-4 fade-in">
                    <div class="card-header bg-light border-bottom">
                        <h4 class="card-title text-dark"><i class="fas fa-plus mr-2"></i> Nueva Categoría</h4>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigo">Código (2-3 letras mayúsculas) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="codigo" name="codigo" maxlength="3" placeholder="Ej. CAT" required value="<?php echo isset($_POST['codigo']) ? htmlspecialchars($_POST['codigo']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" maxlength="255" placeholder="Nombre de la categoría" required value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <!-- Listado de Categorías -->
            <div class="card card-outline card-light fade-in">
                <div class="card-header bg-light border-bottom">
                    <h4 class="card-title text-dark"><i class="fas fa-table mr-2"></i> Categorías Registradas</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover" id="categoriasTable">
                        <thead style="background: #e9ecef;">
                            <tr>
                                <th style="width: 10%;">ID</th>
                                <th style="width: 20%;">Código</th>
                                <th style="width: 50%;">Nombre</th>
                                <th style="width: 20%;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categorias)) { ?>
                                <tr>
                                    <td colspan="4" class="text-center">No se encontraron categorías.</td>
                                </tr>
                            <?php } else { ?>
                                <?php foreach ($categorias as $categoria) { ?>
                                    <tr class="fade-in">
                                        <td><?php echo htmlspecialchars($categoria['id']); ?></td>
                                        <td><?php echo htmlspecialchars($categoria['codigo']); ?></td>
                                        <td><?php echo htmlspecialchars($categoria['nombre']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary edit-categoria" data-id="<?php echo $categoria['id']; ?>" data-codigo="<?php echo htmlspecialchars($categoria['codigo']); ?>" data-nombre="<?php echo htmlspecialchars($categoria['nombre']); ?>" data-toggle="tooltip" title="Editar categoría">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-categoria" data-id="<?php echo $categoria['id']; ?>" data-toggle="tooltip" title="Eliminar categoría">
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
                    <button type="button" class="btn btn-success btn-sm" id="submitFloating" data-toggle="tooltip" title="Crear categoría">
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

    // Validación de código
    const codigoRegex = /^[A-Z]{2,3}$/;

    // Limpiar formulario
    $('#resetForm').click(function() {
        $('#categoriaForm')[0].reset();
        $('.form-control').removeClass('is-valid is-invalid');
        toastr.info('Formulario limpiado.');
    });

    // Editar categoría
    $('.edit-categoria').click(function() {
        const id = $(this).data('id');
        const codigo = $(this).data('codigo');
        const nombre = $(this).data('nombre');
        $('#categoriaForm').attr('action', '<?php echo BASE_URL; ?>categorias/edit');
        $('#categoriaForm').prepend(`<input type="hidden" name="id" value="${id}">`);
        $('#codigo').val(codigo);
        $('#nombre').val(nombre);
        $('#submitForm, #submitFloating').text('Guardar Cambios').removeClass('btn-success').addClass('btn-primary');
        toastr.info('Editando categoría.');
        $('html, body').animate({ scrollTop: 0 }, 300);
    });

    // Eliminar categoría
    $('.delete-categoria').click(function() {
        const id = $(this).data('id');
        if (confirm('¿Estás seguro de eliminar esta categoría? Esta acción no se puede deshacer.')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>categorias/delete',
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success('Categoría eliminada exitosamente.');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(response.error || 'Error al eliminar la categoría.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al eliminar categoría:', xhr.status, xhr.statusText, error);
                    toastr.error('Error al eliminar la categoría: ' + (xhr.statusText || 'Desconocido'));
                }
            });
        }
    });

    // Envío del formulario
    function submitForm(e) {
        e.preventDefault();
        const codigo = $('#codigo').val().trim();
        const nombre = $('#nombre').val().trim();
        if (!codigoRegex.test(codigo)) {
            toastr.error('El código debe ser 2 o 3 letras mayúsculas.');
            $('#codigo').addClass('is-invalid');
            return;
        }
        if (!nombre) {
            toastr.error('El nombre es obligatorio.');
            $('#nombre').addClass('is-invalid');
            return;
        }
        $('#codigo, #nombre').addClass('is-valid');

        const formData = new FormData(document.getElementById('categoriaForm'));
        const action = $('#categoriaForm').attr('action');

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
    $('#categoriaForm').on('submit', function(e) {
        e.preventDefault();
        submitForm(e);
    });

    // Mostrar ayuda
    function mostrarAyuda() {
        toastr.info('1. Ingresa un código de 2 o 3 letras mayúsculas.<br>2. Ingresa el nombre de la categoría.<br>3. Usa los botones para crear, editar o eliminar categorías.', 'Ayuda para Gestión de Categorías');
    }

    // Restaurar formulario al crear nueva categoría
    function resetForm() {
        $('#categoriaForm')[0].reset();
        $('#categoriaForm').attr('action', '<?php echo BASE_URL; ?>categorias/create');
        $('#categoriaForm input[name="id"]').remove();
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
.input-group .form-control {
    height: calc(2.25rem + 2px);
    font-size: 1rem;
}
.input-group-text {
    background: #e9ecef;
}
</style>