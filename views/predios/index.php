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
                    <h3 class="m-0 text-dark"><i class="fas fa-file-import mr-2"></i> Importar Padrón de Predios</h3>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-sm btn-info mr-2" data-toggle="tooltip" title="Ayuda" onclick="mostrarAyuda()">
                        <i class="fas fa-question-circle"></i> Ayuda
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="importPredios" data-toggle="tooltip" title="Importar predios">
                        <i class="fas fa-upload"></i> Importar
                    </button>
                </div>
            </div>
        </div>
    </section>
    <section class="content" style="padding-top: 60px;">
        <div class="container-fluid">
            <div id="alertContainer"></div>
            <!-- Formulario de Importación -->
            <div class="card card-outline card-light mb-4 fade-in">
                <div class="card-header bg-light border-bottom">
                    <h4 class="card-title text-dark"><i class="fas fa-upload mr-2"></i> Subir Archivo de Padrón</h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="importForm" method="POST" action="<?php echo BASE_URL; ?>predios/import" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="padron_file">Archivo de Padrón <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="padron_file" name="padron_file" required>
                                    <label class="custom-file-label" for="padron_file">Seleccionar archivo</label>
                                </div>
                            </div>
                            <small class="form-text text-muted">Selecciona el archivo del padrón de catastro.</small>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Barra de Acción Flotante -->
            <div style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
                <div class="d-flex align-items-center bg-white border rounded p-2 shadow">
                    <button type="button" class="btn btn-success btn-sm" id="importPrediosFloating" data-toggle="tooltip" title="Importar predios">
                        <i class="fas fa-upload mr-1"></i> Importar
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

    // Actualizar etiqueta del archivo seleccionado
    $('#padron_file').on('change', function() {
        const fileName = this.files[0]?.name || 'Seleccionar archivo';
        $(this).next('.custom-file-label').html(fileName);
    });

    // Validar y enviar formulario
    function submitForm() {
        const fileInput = $('#padron_file')[0];
        if (!fileInput.files.length) {
            toastr.error('Por favor, selecciona un archivo.');
            $('#padron_file').addClass('is-invalid');
            return;
        }
        $('#padron_file').addClass('is-valid');
        toastr.info('Importando predios...');

        const formData = new FormData($('#importForm')[0]);
        $.ajax({
            url: '<?php echo BASE_URL; ?>predios/import',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.error || 'Error al importar predios.');
                    $('#alertContainer').html(
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
                console.error('Error al importar predios:', xhr.status, xhr.statusText, error);
                toastr.error('Error al importar predios: ' + (xhr.statusText || 'Desconocido'));
            }
        });
    }

    // Vincular envío a botones
    $('#importPredios, #importPrediosFloating').click(function() {
        submitForm();
    });

    // Mostrar ayuda
    function mostrarAyuda() {
        toastr.info('1. Selecciona el archivo del padrón de catastro.<br>2. Haz clic en "Importar" para procesar los datos.<br>3. Verifica los resultados en "Listado de Predios".', 'Ayuda para Importar Predios');
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
.custom-file-input, .custom-file-label {
    cursor: pointer;
}
.custom-file-label::after {
    background-color: #28a745;