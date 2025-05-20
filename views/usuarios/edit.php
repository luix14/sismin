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
                    <h3 class="m-0 text-dark"><i class="fas fa-user-edit mr-2"></i> Editar Usuario</h3>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?php echo BASE_URL; ?>usuarios" class="btn btn-sm btn-secondary mr-2">
                        <i class="fas fa-arrow-left"></i> Regresar
                    </a>
                </div>
            </div>
        </div>
    </section>
    <section class="content" style="padding-top: 20px;">
        <div class="container-fluid">
            <div class="card card-outline card-light fade-in">
                <div class="card-header bg-light border-bottom">
                    <h4 class="card-title text-dark"><i class="fas fa-user mr-2"></i> Editar <?php echo htmlspecialchars($usuario['username']); ?></h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <form method="POST" action="<?php echo BASE_URL; ?>usuarios/update">
                    <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="username">Usuario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($usuario['username']); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="role_id">Rol <span class="text-danger">*</span></label>
                            <select class="form-control" id="role_id" name="role_id" required>
                                <?php foreach ($roles as $rol) { ?>
                                    <option value="<?php echo $rol['id']; ?>" <?php echo $rol['id'] == $usuario['role_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($rol['nombre']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Permisos Personalizados</label>
                            <?php
                            // Agrupar permisos por módulo
                            $permisosPorModulo = [];
                            foreach ($permisos as $permiso) {
                                $modulo = $permiso['modulo'] ?? 'Sin módulo';
                                $permisosPorModulo[$modulo][] = $permiso;
                            }
                            foreach ($permisosPorModulo as $modulo => $moduloPermisos) { ?>
                                <h5 class="mt-4">Módulo: <?php echo htmlspecialchars(ucfirst($modulo)); ?></h5>
                                <table class="table table-bordered table-hover">
                                    <thead style="background: #e9ecef;">
                                        <tr>
                                            <th>Permiso</th>
                                            <th>Descripción</th>
                                            <th>Habilitado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($moduloPermisos as $permiso) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($permiso['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($permiso['descripcion'] ?? 'Sin descripción'); ?></td>
                                                <td>
                                                    <input type="checkbox" name="permisos[<?php echo $permiso['id']; ?>]" value="1" 
                                                           <?php echo isset($usuarioPermisos[$permiso['id']]) && $usuarioPermisos[$permiso['id']] ? 'checked' : ''; ?>>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo BASE_URL; ?>usuarios" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar</button>
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
</style>