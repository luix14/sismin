<?php
if (!defined('BASE_URL')) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}
require_once 'models/Usuario.php';
$permissions = [];
$isAdmin = false;
if (isset($_SESSION['user_id']) && isset($pdo)) {
    try {
        $stmt = $pdo->prepare('SELECT role_id, username FROM usuarios WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $userRole = $user['role_id'] ?? null;
        $username = $user['username'] ?? '';

        error_log('Usuario ID=' . $_SESSION['user_id'] . ', Username=' . $username . ', Role_id=' . ($userRole ?? 'null'));

        if ($userRole === 4 || strtolower($username) === 'admin') {
            $isAdmin = true;
            $permissions = [
                'view_predios', 'import_predios', 'view_contribuyentes', 'create_contribuyentes', 'update_contribuyentes',
                'view_cobros', 'create_cobros', 'edit_cobros', 'delete_cobros',
                'view_derechos', 'create_derechos', 'edit_derechos', 'delete_derechos',
                'view_categorias', 'create_categorias', 'edit_categorias', 'delete_categorias',
                'view_configuraciones', 'edit_configuraciones', 'view_uma', 'create_uma', 'edit_uma', 'delete_uma',
                'view_usuarios', 'create_usuarios', 'edit_usuarios', 'delete_usuarios'
            ];
            try {
                $stmt = $pdo->query('SELECT nombre FROM permisos');
                $dbPermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $permissions = array_unique(array_merge($permissions, $dbPermissions));
                error_log('Admin: Permisos combinados de tabla y estáticos: ' . json_encode($permissions));
            } catch (Exception $e) {
                error_log('Admin: Error al obtener permisos de tabla, usando estáticos: ' . $e->getMessage());
            }
            error_log('Usuario con role_id=4 o admin, asignados permisos: ' . json_encode($permissions));
        } else {
            $usuario = new Usuario($pdo);
            $permissions = $usuario->getUserPermissions($_SESSION['user_id']);
            error_log('Permisos obtenidos para user_id=' . $_SESSION['user_id'] . ': ' . json_encode($permissions));
        }
    } catch (Exception $e) {
        error_log('Error al obtener permisos en sidebar.php: ' . $e->getMessage());
        if (isset($_SESSION['username']) && strtolower($_SESSION['username']) === 'admin') {
            $isAdmin = true;
            $permissions = [
                'view_predios', 'import_predios', 'view_contribuyentes', 'create_contribuyentes', 'update_contribuyentes',
                'view_cobros', 'create_cobros', 'edit_cobros', 'delete_cobros',
                'view_derechos', 'create_derechos', 'edit_derechos', 'delete_derechos',
                'view_categorias', 'create_categorias', 'edit_categorias', 'delete_categorias',
                'view_configuraciones', 'edit_configuraciones', 'view_uma', 'create_uma', 'edit_uma', 'delete_uma',
                'view_usuarios', 'create_usuarios', 'edit_usuarios', 'delete_usuarios'
            ];
            error_log('Fallback para admin: asignados permisos estáticos: ' . json_encode($permissions));
        } else {
            $permissions = ['view_cobros'];
            error_log('Fallback para usuario no admin: ' . json_encode($permissions));
        }
    }
} else {
    error_log('No se pudo inicializar permisos en sidebar.php: user_id o pdo no definidos');
    if (isset($_SESSION['username']) && strtolower($_SESSION['username']) === 'admin') {
        $isAdmin = true;
        $permissions = [
            'view_predios', 'import_predios', 'view_contribuyentes', 'create_contribuyentes', 'update_contribuyentes',
            'view_cobros', 'create_cobros', 'edit_cobros', 'delete_cobros',
            'view_derechos', 'create_derechos', 'edit_derechos', 'delete_derechos',
            'view_categorias', 'create_categorias', 'edit_categorias', 'delete_categorias',
            'view_configuraciones', 'edit_configuraciones', 'view_uma', 'create_uma', 'edit_uma', 'delete_uma',
            'view_usuarios', 'create_usuarios', 'edit_usuarios', 'delete_usuarios'
        ];
        error_log('Fallback para admin sin PDO: asignados permisos estáticos: ' . json_encode($permissions));
    }
}
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?php echo BASE_URL; ?>dashboard" class="brand-link">
        <img src="<?php echo BASE_URL; ?>vendor/adminlte/dist/img/AdminLTELogo.png" alt="SISMIN Logo" class="brand-image img-circle elevation-3">
        <span class="brand-text font-weight-bold">SISMIN</span>
    </a>
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>dashboard" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <?php
                $predialPerms = $isAdmin || in_array('view_predios', $permissions) || in_array('import_predios', $permissions) || in_array('view_contribuyentes', $permissions);
                if ($predialPerms) { ?>
                    <li class="nav-item has-treeview" id="predial">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-home"></i>
                            <p>Predial<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if ($isAdmin || in_array('import_predios', $permissions)) { ?>
                                <li class="nav-item">
                                    <a href="<?php echo BASE_URL; ?>predios" class="nav-link">
                                        <i class="fas fa-file-import nav-icon"></i>
                                        <p>Importar Predios</p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if ($isAdmin || in_array('view_predios', $permissions)) { ?>
                                <li class="nav-item">
                                    <a href="<?php echo BASE_URL; ?>predios/list" class="nav-link">
                                        <i class="fas fa-list-ul nav-icon"></i>
                                        <p>Listar Predios</p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if ($isAdmin || in_array('view_contribuyentes', $permissions)) { ?>
                                <li class="nav-item">
                                    <a href="<?php echo BASE_URL; ?>contribuyentes" class="nav-link">
                                        <i class="fas fa-user-tie nav-icon"></i>
                                        <p>Contribuyentes</p>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
                <?php
                $pagoPerms = $isAdmin || in_array('view_cobros', $permissions) || in_array('create_cobros', $permissions) || 
                             in_array('view_derechos', $permissions) || in_array('view_categorias', $permissions);
                if ($pagoPerms) { ?>
                    <li class="nav-item has-treeview" id="pago-derechos">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-money-bill"></i>
                            <p>Pago de Derechos<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if ($isAdmin || in_array('create_cobros', $permissions)) { ?>
                                <li class="nav-item">
                                    <a href="<?php echo BASE_URL; ?>cobro" class="nav-link">
                                        <i class="fas fa-cash-register nav-icon"></i>
                                        <p>Cobros</p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if ($isAdmin || in_array('view_cobros', $permissions)) { ?>
                                <li class="nav-item">
                                    <a href="<?php echo BASE_URL; ?>cobro/list" class="nav-link">
                                        <i class="fas fa-list nav-icon"></i>
                                        <p>Listado de Cobros</p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if ($isAdmin || in_array('view_derechos', $permissions)) { ?>
                                <li class="nav-item">
                                    <a href="<?php echo BASE_URL; ?>derechos" class="nav-link">
                                        <i class="fas fa-file-invoice nav-icon"></i>
                                        <p>Catálogo de Derechos</p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if ($isAdmin || in_array('view_categorias', $permissions)) { ?>
                                <li class="nav-item">
                                    <a href="<?php echo BASE_URL; ?>categorias" class="nav-link">
                                        <i class="fas fa-tags nav-icon"></i>
                                        <p>Catálogo de Categorías</p>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
                <?php
                $configPerms = $isAdmin || in_array('view_configuraciones', $permissions) || in_array('edit_configuraciones', $permissions) || 
                               in_array('view_uma', $permissions) || in_array('view_usuarios', $permissions);
                if ($configPerms) { ?>
                    <li class="nav-item has-treeview" id="configuracion">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>Configuración<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if ($isAdmin || in_array('view_configuraciones', $permissions) || in_array('edit_configuraciones', $permissions)) { ?>
                                <li class="nav-item">
                                    <a href="<?php echo BASE_URL; ?>configuraciones/municipio" class="nav-link">
                                        <i class="fas fa-city nav-icon"></i>
                                        <p>Datos del Municipio</p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if ($isAdmin || in_array('view_uma', $permissions)) { ?>
                                <li class="nav-item">
                                    <a href="<?php echo BASE_URL; ?>uma" class="nav-link">
                                        <i class="fas fa-money-check-alt nav-icon"></i>
                                        <p>UMA</p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if ($isAdmin || in_array('view_usuarios', $permissions)) { ?>
                                <li class="nav-item">
                                    <a href="<?php echo BASE_URL; ?>usuarios" class="nav-link">
                                        <i class="fas fa-users nav-icon"></i>
                                        <p>Gestión de Usuarios</p>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </nav>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;

    const menuMap = [
        <?php if ($isAdmin || in_array('create_cobros', $permissions) || in_array('view_cobros', $permissions)) { ?>
            { path: '/sismin/cobro', parentId: 'pago-derechos' },
        <?php } ?>
        <?php if ($isAdmin || in_array('view_cobros', $permissions)) { ?>
            { path: '/sismin/cobro/list', parentId: 'pago-derechos' },
        <?php } ?>
        <?php if ($isAdmin || in_array('view_derechos', $permissions)) { ?>
            { path: '/sismin/derechos', parentId: 'pago-derechos' },
        <?php } ?>
        <?php if ($isAdmin || in_array('view_categorias', $permissions)) { ?>
            { path: '/sismin/categorias', parentId: 'pago-derechos' },
        <?php } ?>
        <?php if ($isAdmin || in_array('import_predios', $permissions)) { ?>
            { path: '/sismin/predios', parentId: 'predial' },
        <?php } ?>
        <?php if ($isAdmin || in_array('view_predios', $permissions)) { ?>
            { path: '/sismin/predios/list', parentId: 'predial' },
        <?php } ?>
        <?php if ($isAdmin || in_array('view_contribuyentes', $permissions)) { ?>
            { path: '/sismin/contribuyentes', parentId: 'predial' },
        <?php } ?>
        <?php if ($isAdmin || in_array('view_configuraciones', $permissions) || in_array('edit_configuraciones', $permissions)) { ?>
            { path: '/sismin/configuraciones/municipio', parentId: 'configuracion' },
        <?php } ?>
        <?php if ($isAdmin || in_array('view_uma', $permissions)) { ?>
            { path: '/sismin/uma', parentId: 'configuracion' },
        <?php } ?>
        <?php if ($isAdmin || in_array('view_usuarios', $permissions)) { ?>
            { path: '/sismin/usuarios', parentId: 'configuracion' },
        <?php } ?>
    ];

    // Activar menú basado en la ruta actual
    menuMap.forEach(function(menu) {
        if (currentPath === menu.path || currentPath.startsWith(menu.path + '/')) {
            const parentMenu = document.getElementById(menu.parentId);
            if (parentMenu) {
                parentMenu.classList.add('menu-open');
                const parentLink = parentMenu.querySelector('.nav-link');
                if (parentLink) parentLink.classList.add('active');
                const subMenuLink = parentMenu.querySelector(`a[href="${currentPath}"]`);
                if (subMenuLink) subMenuLink.classList.add('active');
            }
        }
    });

    if (currentPath === '/sismin/dashboard') {
        const dashboardLink = document.querySelector('a[href="<?php echo BASE_URL; ?>dashboard"]');
        if (dashboardLink) dashboardLink.classList.add('active');
    }

    // Inicializar treeview de AdminLTE
    $('[data-widget="treeview"]').Treeview('init');

    // Colapsar sidebar al seleccionar un enlace en móviles (excepto menús padres)
    if (window.innerWidth <= 767.98) {
        $('.nav-link').on('click', function(e) {
            if (!$(this).parent().hasClass('has-treeview')) {
                setTimeout(() => {
                    $('[data-widget="pushmenu"]').PushMenu('collapse');
                }, 200);
            }
        });
    }
});
</script>

<style>
.main-sidebar {
    background: linear-gradient(180deg, #0097a6 0%, #006f7b 100%); /* Degradado cian a cian oscuro */
    width: 250px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease-in-out;
}
.brand-link {
    position: relative;
    background: #0097a6; /* Color principal cian */
    border-bottom: 2px solid #cd42a5; /* Línea magenta */
    padding: 15px 20px;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
    z-index: 1;
}
.brand-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100vw; /* Extiende el fondo al ancho completo */
    height: 100%;
    background: linear-gradient(90deg, #0097a6 0%, #006f7b 100%); /* Degradado cian a cian oscuro */
    border-bottom: 2px solid #cd42a5; /* Línea magenta */
    z-index: -1;
}
.brand-link:hover {
    background: #cd42a5; /* Hover magenta */
    transform: translateY(-2px);
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
}
.brand-link .brand-image {
    transform: scale(1.2);
    transition: transform 0.3s ease;
}
.brand-link:hover .brand-image {
    transform: scale(1.3);
}
.brand-text {
    font-size: 1.5rem;
    color: #fff;
    font-weight: 700;
    transition: color 0.3s ease;
}
.brand-link:hover .brand-text {
    color: #ffffff;
}
.nav-link {
    display: flex;
    align-items: center;
    padding: 10px 15px;
    color: #ffffff;
    font-weight: 600;
    border-radius: 6px;
    margin: 4px 10px;
    transition: all 0.3s ease;
}
.nav-link:hover {
    background: #0097a6; /* Color principal cian */
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
}
.nav-link.active {
    background: #0097a6 !important; /* Color principal cian */
    color: #ffffff !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
.nav-icon {
    font-size: 1.4rem;
    margin-right: 10px;
    width: 24px;
    text-align: center;
    transition: transform 0.3s ease;
}
.nav-link:hover .nav-icon {
    transform: scale(1.15);
}
.nav-treeview .nav-link {
    padding-left: 40px;
    font-size: 0.9rem;
    font-weight: 400;
    background: rgba(0, 0, 0, 0.1);
}
.nav-treeview {
    padding: 5px 0;
}
.nav-item.menu-open .nav-treeview {
    display: block;
}
.right.fa-angle-left {
    font-size: 1.2rem;
    transition: transform 0.3s ease;
}
.nav-item.menu-open .right.fa-angle-left {
    transform: rotate(-90deg);
}
@media (max-width: 767.98px) {
    .main-sidebar {
        width: 200px;
    }
    .brand-link {
        padding: 12px 15px;
    }
    .brand-link::before {
        width: 100%; /* En móviles, el fondo no se extiende */
    }
    .brand-text {
        font-size: 1.3rem;
    }
    .brand-link .brand-image {
        transform: scale(1.1);
    }
    .brand-link:hover .brand-image {
        transform: scale(1.2);
    }
    .nav-link p {
        font-size: 0.85rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100px;
    }
    .nav-icon {
        font-size: 1.6rem;
        width: 26px;
    }
    .nav-treeview .nav-link {
        padding-left: 35px;
        font-size: 0.85rem;
    }
}
</style>