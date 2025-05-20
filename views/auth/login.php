<?php
if (!defined('BASE_URL')) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}
?>
<div class="login-box fade-in">
    <div class="login-logo">
        <a href="<?php echo BASE_URL; ?>"><b>SISMIN</b></a>
    </div>
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Inicia sesión para continuar</p>
            <?php if (isset($error)) { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php } ?>
            <form action="<?php echo BASE_URL; ?>login" method="POST" id="loginForm">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="username" placeholder="Usuario" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
                    </div>
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
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3000
    };
    <?php if (isset($error)) { ?>
        toastr.error('<?php echo htmlspecialchars($error); ?>');
    <?php } ?>

    // Depuración del formulario
    $('#loginForm').on('submit', function(e) {
        const username = $('input[name="username"]').val();
        const password = $('input[name="password"]').val();
        console.log('Enviando login:', { username: username, password: password ? '[hidden]' : '' });
    });
});
</script>