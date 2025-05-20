<?php
if (!defined('BASE_URL')) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}
?>
<div class="content-wrapper" style="background: #f4f6f9;">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Cambiar Contraseña</h1>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actualizar Contraseña</h3>
                </div>
                <form method="POST" action="<?php echo BASE_URL; ?>usuarios/change-password">
                    <div class="card-body">
                        <?php if (isset($_GET['error'])) { ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php } elseif (isset($_GET['success'])) { ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="user_id">Usuario <span class="text-danger">*</span></label>
                            <select class="form-control" id="user_id" name="user_id" required>
                                <option value="">Selecciona un usuario</option>
                                <?php foreach ($users as $user) { ?>
                                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="password">Nueva Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" maxlength="255" placeholder="Nueva contraseña" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirmar Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" maxlength="255" placeholder="Confirma la contraseña" required>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo BASE_URL; ?>usuarios" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
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
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3000
    };
    <?php if (isset($_GET['error'])) { ?>
        toastr.error('<?php echo htmlspecialchars($_GET['error']); ?>');
    <?php } elseif (isset($_GET['success'])) { ?>
        toastr.success('<?php echo htmlspecialchars($_GET['success']); ?>');
    <?php } ?>
});
</script>