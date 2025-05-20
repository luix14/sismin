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
                    <h3 class="m-0 text-dark"><i class="fas fa-edit mr-2"></i> Editar Cobro (Folio: <?php echo htmlspecialchars($cobro['folio']); ?>)</h3>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-sm btn-info mr-2" data-toggle="tooltip" title="Ayuda" onclick="mostrarAyuda()">
                        <i class="fas fa-question-circle"></i> Ayuda
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary mr-2" id="resetForm" data-toggle="tooltip" title="Limpiar formulario">
                        <i class="fas fa-undo"></i> Limpiar
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="submitForm" data-toggle="tooltip" title="Guardar cambios">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </section>
    <section class="content" style="padding-top: 60px;">
        <div class="container-fluid">
            <div id="alertContainer">
                <?php if (isset($success)) { ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($success); ?>
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
            <form id="cobroForm" method="POST" action="<?php echo BASE_URL; ?>cobro/update">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($cobro['id']); ?>">
                <!-- Datos del Contribuyente -->
                <div class="card card-outline card-light mb-4 fade-in">
                    <div class="card-header bg-light border-bottom">
                        <h4 class="card-title text-dark"><i class="fas fa-user mr-2"></i> Datos del Contribuyente</h4>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contribuyente">Buscar Contribuyente (RFC o Nombre) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                        <input type="text" class="form-control" id="contribuyente" list="contribuyentesList" placeholder="Escribe RFC o nombre del contribuyente" autocomplete="off" data-toggle="tooltip" title="Busca un contribuyente registrado" value="<?php echo htmlspecialchars($cobro['nombre_contribuyente']); ?>">
                                        <input type="hidden" id="rfc" name="rfc" value="<?php echo htmlspecialchars($cobro['rfc']); ?>">
                                    </div>
                                    <datalist id="contribuyentesList"></datalist>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" maxlength="255" placeholder="Nombre del contribuyente" required value="<?php echo htmlspecialchars($cobro['nombre_contribuyente']); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input type="text" class="form-control" id="telefono" name="telefono" maxlength="20" placeholder="Ej. 555-123-4567" value="<?php echo htmlspecialchars($cobro['telefono'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="direccion">Dirección <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="direccion" name="direccion" maxlength="255" placeholder="Dirección del contribuyente" required value="<?php echo htmlspecialchars($cobro['direccion_contribuyente']); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Derechos -->
                <div class="card card-outline card-light mb-4 fade-in">
                    <div class="card-header bg-light border-bottom">
                        <h4 class="card-title text-dark"><i class="fas fa-list-ul mr-2"></i> Derechos a Cobrar</h4>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="derecho">Añadir Derecho <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" class="form-control" id="derecho" list="derechosList" placeholder="Escribe código o descripción del derecho" autocomplete="off" data-toggle="tooltip" title="Busca un derecho">
                            </div>
                            <datalist id="derechosList"></datalist>
                        </div>
                        <table class="table table-bordered table-hover" id="derechosTable">
                            <thead style="background: #e9ecef;">
                                <tr>
                                    <th style="width: 5%;">No.</th>
                                    <th style="width: 15%;">Código</th>
                                    <th style="width: 30%;">Descripción</th>
                                    <th style="width: 10%;">Cantidad</th>
                                    <th style="width: 15%;">Precio Unitario</th>
                                    <th style="width: 10%;">Descuento</th>
                                    <th style="width: 15%;">Total</th>
                                    <th style="width: 10%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-right">Total:</th>
                                    <th id="totalImporte">$0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                        <button type="button" class="btn btn-sm btn-secondary mt-2" id="clearTable"><i class="fas fa-trash mr-1"></i> Limpiar Derechos</button>
                    </div>
                </div>
                <!-- Observaciones -->
                <div class="card card-outline card-light mb-4 fade-in">
                    <div class="card-header bg-light border-bottom">
                        <h4 class="card-title text-dark"><i class="fas fa-comment mr-2"></i> Observaciones</h4>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3" placeholder="Añade notas adicionales (opcional)" data-toggle="tooltip" title="Notas sobre el cobro"><?php echo htmlspecialchars($cobro['observaciones'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                <!-- Barra de Acción Flotante -->
                <div style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
                    <div class="d-flex align-items-center bg-white border rounded p-2 shadow">
                        <span class="mr-3 text-dark">Total: <strong id="floatingTotal">$0.00</strong></span>
                        <button type="button" class="btn btn-success btn-sm" id="submitFloating" data-toggle="tooltip" title="Confirmar y guardar cambios">
                            <i class="fas fa-save mr-1"></i> Confirmar
                        </button>
                    </div>
                </div>
            </form>
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

    // Validación de RFC
    const rfcRegex = /^([A-ZÑ&]{3,4})?\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])([A-Z0-9]{3})$|^XAXX010101000$/;

    // Función de debounce para limitar solicitudes AJAX
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Búsqueda de contribuyentes
    const searchContribuyentes = debounce(function(query) {
        if (query.length < 1) {
            $('#contribuyentesList').empty();
            return;
        }
        $.ajax({
            url: '<?php echo BASE_URL; ?>contribuyentes/search',
            method: 'GET',
            data: { term: query },
            dataType: 'json',
            success: function(data) {
                console.log('Respuesta contribuyentes:', data);
                $('#contribuyentesList').empty();
                if (data && data.results && Array.isArray(data.results)) {
                    data.results.forEach(function(item) {
                        if (item.rfc && item.text) {
                            $('#contribuyentesList').append(
                                `<option value="${item.text}" data-rfc="${item.rfc}" data-nombre="${item.nombre || ''}" data-direccion="${item.direccion_fiscal || ''}" data-telefono="${item.telefono || ''}">${item.text}</option>`
                            );
                        }
                    });
                    if (data.results.length === 0) {
                        toastr.info('No se encontraron contribuyentes para: ' + query);
                    }
                } else {
                    console.error('Respuesta inválida del servidor:', data);
                    toastr.error('Respuesta inválida al buscar contribuyentes.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en búsqueda de contribuyente:', xhr.status, xhr.statusText, error);
                toastr.error('Error al buscar contribuyente: ' + (xhr.statusText || 'Desconocido'));
            }
        });
    }, 100);

    $('#contribuyente').on('input', function() {
        const query = $(this).val().trim();
        searchContribuyentes(query);
    }).on('change', function() {
        const selectedOption = $('#contribuyentesList option').filter(function() {
            return $(this).val() === $('#contribuyente').val();
        });
        if (selectedOption.length) {
            $('#rfc').val(selectedOption.data('rfc'));
            $('#nombre').val(selectedOption.data('nombre')).addClass('is-valid');
            $('#telefono').val(selectedOption.data('telefono')).addClass('is-valid');
            $('#direccion').val(selectedOption.data('direccion')).addClass('is-valid');
            toastr.success('Contribuyente seleccionado.');
        } else {
            $('#rfc, #nombre, #telefono, #direccion').val('').removeClass('is-valid is-invalid');
        }
    });

    // Búsqueda de derechos
    const searchDerechos = debounce(function(query) {
        if (query.length < 1) {
            $('#derechosList').empty();
            return;
        }
        $.ajax({
            url: '<?php echo BASE_URL; ?>derechos/search',
            method: 'GET',
            data: { term: query },
            dataType: 'json',
            success: function(data) {
                console.log('Respuesta derechos:', data);
                $('#derechosList').empty();
                if (data && data.results && Array.isArray(data.results)) {
                    data.results.forEach(function(item) {
                        if (item.id && item.codigo && item.descripcion && item.costo !== undefined) {
                            const displayText = `${item.codigo} - ${item.descripcion} - $${parseFloat(item.costo).toFixed(2)}`;
                            console.log('Añadiendo opción al datalist:', displayText);
                            $('#derechosList').append(
                                `<option value="${displayText}" data-id="${item.id}" data-costo="${parseFloat(item.costo)}" data-codigo="${item.codigo}" data-descripcion="${item.descripcion}">${displayText}</option>`
                            );
                        } else {
                            console.warn('Item de derecho inválido:', item);
                        }
                    });
                    if (data.results.length === 0) {
                        toastr.info('No se encontraron derechos para: "' + query + '"');
                    }
                } else {
                    console.error('Respuesta inválida del servidor:', data);
                    toastr.error('Respuesta inválida al buscar derechos.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en búsqueda de derechos:', xhr.status, xhr.statusText, error);
                toastr.error('Error al buscar derechos: ' + (xhr.statusText || 'Desconocido'));
            }
        });
    }, 100);

    $('#derecho').on('input', function() {
        const query = $(this).val().trim();
        searchDerechos(query);
    }).on('change', function() {
        const selectedOption = $('#derechosList option').filter(function() {
            return $(this).val() === $('#derecho').val();
        });
        if (selectedOption.length) {
            derechos.push({
                id: selectedOption.data('id'),
                codigo: selectedOption.data('codigo'),
                descripcion: selectedOption.data('descripcion'),
                costo: parseFloat(selectedOption.data('costo')),
                cantidad: 1,
                descuento: 0
            });
            updateTable();
            $(this).val('').focus();
            toastr.success('Derecho añadido a la tabla.');
        }
    });

    // Tabla dinámica
    let derechos = [];
    // Normalizar datos de derechos desde PHP
    <?php if (!empty($cobro['derechos'])): ?>
        const rawDerechos = <?php echo json_encode($cobro['derechos'] ?? []); ?>;
        console.log('Derechos crudos:', rawDerechos); // Depuración
        derechos = rawDerechos.map(item => ({
            id: item.id,
            codigo: item.codigo,
            descripcion: item.descripcion,
            costo: parseFloat(item.precio_unitario) || 0,
            cantidad: parseInt(item.cantidad) || 1,
            descuento: parseFloat(item.descuento) || 0
        }));
        console.log('Derechos normalizados:', derechos); // Depuración
    <?php endif; ?>

    function updateTable() {
        const tbody = $('#derechosTable tbody');
        tbody.empty();
        let total = 0;
        if (!Array.isArray(derechos) || derechos.length === 0) {
            console.warn('No hay derechos para mostrar');
            tbody.append('<tr><td colspan="8" class="text-center">No se han añadido derechos.</td></tr>');
        } else {
            derechos.forEach((item, index) => {
                if (!item.costo || !item.cantidad) {
                    console.warn('Derecho inválido en índice', index, item);
                    return;
                }
                const importeTotal = (item.costo * item.cantidad) - (item.descuento || 0);
                total += importeTotal;
                tbody.append(`
                    <tr class="fade-in">
                        <td>${index + 1}</td>
                        <td>${item.codigo}</td>
                        <td>${item.descripcion}</td>
                        <td><input type="number" class="form-control form-control-sm cantidad" data-index="${index}" value="${item.cantidad}" min="1" style="width: 80px;"></td>
                        <td class="text-right">$${item.costo.toFixed(2)}</td>
                        <td><input type="number" class="form-control form-control-sm descuento" data-index="${index}" value="${item.descuento || 0}" min="0" step="0.01" style="width: 80px;"></td>
                        <td class="text-right">$${importeTotal.toFixed(2)}</td>
                        <td><button type="button" class="btn btn-sm btn-danger remove-derecho" data-index="${index}"><i class="fas fa-trash"></i></button></td>
                    </tr>
                `);
            });
        }
        $('#totalImporte, #floatingTotal').text(`$${total.toFixed(2)}`);
    }

    // Precargar derechos
    $(document).ready(function() {
        updateTable();
    });

    // Actualizar cantidad
    $('#derechosTable').on('change', '.cantidad', function() {
        const index = $(this).data('index');
        const cantidad = parseInt($(this).val()) || 1;
        derechos[index].cantidad = cantidad;
        updateTable();
    });

    // Actualizar descuento
    $('#derechosTable').on('change', '.descuento', function() {
        const index = $(this).data('index');
        const descuento = parseFloat($(this).val()) || 0;
        derechos[index].descuento = descuento;
        updateTable();
    });

    // Eliminar derecho
    $('#derechosTable').on('click', '.remove-derecho', function() {
        const index = $(this).data('index');
        derechos.splice(index, 1);
        updateTable();
        toastr.info('Derecho eliminado.');
    });

    // Limpiar tabla
    $('#clearTable').click(function() {
        derechos = [];
        updateTable();
        toastr.info('Tabla de derechos limpiada.');
    });

    // Limpiar formulario
    $('#resetForm').click(function() {
        $('#cobroForm')[0].reset();
        // Restaurar datos originales
        <?php if (!empty($cobro['derechos'])): ?>
            derechos = rawDerechos.map(item => ({
                id: item.id,
                codigo: item.codigo,
                descripcion: item.descripcion,
                costo: parseFloat(item.precio_unitario) || 0,
                cantidad: parseInt(item.cantidad) || 1,
                descuento: parseFloat(item.descuento) || 0
            }));
        <?php else: ?>
            derechos = [];
        <?php endif; ?>
        updateTable();
        $('#contribuyente').val('<?php echo htmlspecialchars($cobro['nombre_contribuyente']); ?>');
        $('#rfc').val('<?php echo htmlspecialchars($cobro['rfc']); ?>');
        $('#nombre').val('<?php echo htmlspecialchars($cobro['nombre_contribuyente']); ?>');
        $('#telefono').val('<?php echo htmlspecialchars($cobro['telefono'] ?? ''); ?>');
        $('#direccion').val('<?php echo htmlspecialchars($cobro['direccion_contribuyente']); ?>');
        $('#observaciones').val('<?php echo htmlspecialchars($cobro['observaciones'] ?? ''); ?>');
        $('.form-control').removeClass('is-valid is-invalid');
        toastr.info('Formulario restaurado.');
    });

    // Mostrar ayuda
    function mostrarAyuda() {
        toastr.info('1. Escribe el RFC o nombre para buscar un contribuyente.<br>2. Escribe el código o descripción para añadir derechos.<br>3. Ajusta cantidades y descuentos en la tabla.<br>4. Confirma para guardar los cambios.', 'Ayuda para Editar Cobro');
    }

    // Envío del formulario
    function submitForm(e) {
        e.preventDefault();
        const rfc = $('#rfc').val().trim();
        const nombre = $('#nombre').val().trim();
        const direccion = $('#direccion').val().trim();
        const derechosCount = derechos.length;
        if (!rfc || !rfcRegex.test(rfc)) {
            toastr.error('Por favor, ingresa un RFC válido.');
            $('#rfc').addClass('is-invalid');
            return;
        }
        if (!nombre || !direccion) {
            toastr.error('Nombre y Dirección son obligatorios.');
            if (!nombre) $('#nombre').addClass('is-invalid');
            if (!direccion) $('#direccion').addClass('is-invalid');
            return;
        }
        if (derechosCount === 0) {
            toastr.error('Por favor, selecciona al menos un derecho.');
            return;
        }
        $('#rfc, #nombre, #direccion').addClass('is-valid');

        const formData = new FormData(document.getElementById('cobroForm'));
        formData.append('derechos', JSON.stringify(derechos));

        toastr.info('Guardando cambios...');
        $.ajax({
            url: '<?php echo BASE_URL; ?>cobro/update',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta del servidor:', response);
                if (response.success) {
                    toastr.success('Cobro actualizado exitosamente.');
                    setTimeout(function() {
                        window.location.href = '<?php echo BASE_URL; ?>cobro/list';
                    }, 1000);
                } else {
                    console.error('Respuesta inválida:', response);
                    toastr.error(response.error || 'Error al actualizar el cobro.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en envío del formulario:', xhr.status, xhr.statusText, error);
                toastr.error('Error al actualizar el cobro: ' + (xhr.statusText || 'Desconocido'));
            }
        });
    }

    // Vincular envío a botones
    $('#submitForm, #submitFloating').click(function(e) {
        submitForm(e);
    });

    // Prevenir envío tradicional del formulario
    $('#cobroForm').on('submit', function(e) {
        e.preventDefault();
        submitForm(e);
    });

    // Atajos de teclado
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            submitForm(e);
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
.form-control.is-valid, .form-control.is-invalid {
    transition: border-color 0.3s;
}
.table-hover tbody tr:hover {
    background-color: #f1f3f5;
    transition: background-color 0.2s;
}
.form-control-sm {
    height: calc(1.5em + 0.5rem + 2px);
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