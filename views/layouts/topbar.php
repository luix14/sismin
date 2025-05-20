<?php
if (!defined('BASE_URL')) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}
?>
<nav class="main-header navbar navbar-expand navbar-dark">
    <div class="container-fluid">
        <ul class="navbar-nav left-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button" data-toggle="tooltip" title="Menú Lateral">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>
        <ul class="navbar-nav right-nav">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>logout" data-toggle="tooltip" title="Cerrar Sesión">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
.main-header {
    background: linear-gradient(90deg, #0097a6 0%, #006f7b 100%); /* Degradado cian a cian oscuro */
    border-bottom: 2px solid #cd42a5; /* Línea magenta */
    padding: 0.6rem 1rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}
.navbar-nav {
    display: flex;
    align-items: center;
    margin: 0;
}
.navbar-nav.left-nav {
    margin-right: auto;
}
.navbar-nav.right-nav {
    margin-left: auto;
}
.nav-link {
    display: flex;
    align-items: center;
    padding: 0.5rem 0.8rem;
    color: #ffffff;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.3s ease;
}
.nav-link:hover {
    background: #0097a6; /* Color principal cian */
    color: #ffffff !important;
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
}
.nav-link .fas {
    font-size: 1.3rem;
    margin-right: 6px;
    transition: transform 0.3s ease;
}
.nav-link:hover .fas {
    transform: scale(1.15);
}
.nav-link[title="Cerrar Sesión"] {
    font-size: 0.95rem;
}
@media (max-width: 767.98px) {
    .main-header {
        padding: 0.5rem;
    }
    .nav-link {
        padding: 0.4rem 0.6rem;
    }
    .nav-link .fas {
        font-size: 1.5rem;
        margin-right: 4px;
    }
    .nav-link[title="Cerrar Sesión"] {
        font-size: 0.85rem;
    }
    .nav-link[title="Cerrar Sesión"] span {
        display: none; /* Oculta texto en móviles, solo ícono */
    }
}
</style>