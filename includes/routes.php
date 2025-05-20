<?php
$routes = [
    // Autenticación
    '/sismin' => ['controller' => 'AuthController', 'method' => 'index'],
    '/sismin/' => ['controller' => 'AuthController', 'method' => 'index'],
    '/sismin/login' => ['controller' => 'AuthController', 'method' => 'index'],
    '/sismin/logout' => ['controller' => 'AuthController', 'method' => 'logout'],

    // Dashboard
    '/sismin/dashboard' => ['controller' => 'DashboardController', 'method' => 'index'],
    '/sismin/dashboard/stats' => ['controller' => 'DashboardController', 'method' => 'stats'],
    '/sismin/dashboard/prediosPorMunicipio' => ['controller' => 'DashboardController', 'method' => 'prediosPorMunicipio'],

    // Predios
    '/sismin/predios' => ['controller' => 'PredioController', 'method' => 'index'],
    '/sismin/predios/import' => ['controller' => 'PredioController', 'method' => 'import'],
    '/sismin/predios/list' => ['controller' => 'PredioController', 'method' => 'list'],

    // Contribuyentes
    '/sismin/contribuyentes' => ['controller' => 'ContribuyenteController', 'method' => 'index'],
    '/sismin/contribuyentes/update' => ['controller' => 'ContribuyenteController', 'method' => 'update'],
    '/sismin/contribuyentes/create' => ['controller' => 'ContribuyenteController', 'method' => 'create'],
    '/sismin/contribuyentes/search' => ['controller' => 'ContribuyenteController', 'method' => 'search'],

    // Cobros
    '/sismin/cobro' => ['controller' => 'CobroController', 'method' => 'index'],
    '/sismin/cobro/create' => ['controller' => 'CobroController', 'method' => 'create'],
    '/sismin/cobro/receipt' => ['controller' => 'CobroController', 'method' => 'receipt'],
    '/sismin/cobro/edit' => ['controller' => 'CobroController', 'method' => 'edit'],
    '/sismin/cobro/update' => ['controller' => 'CobroController', 'method' => 'update'],
    '/sismin/cobro/delete' => ['controller' => 'CobroController', 'method' => 'delete'],
    '/sismin/cobro/list' => ['controller' => 'CobroController', 'method' => 'list'],

    // Derechos
    '/sismin/derechos' => ['controller' => 'DerechoController', 'method' => 'index'],
    '/sismin/derechos/create' => ['controller' => 'DerechoController', 'method' => 'create'],
    '/sismin/derechos/edit' => ['controller' => 'DerechoController', 'method' => 'edit'],
    '/sismin/derechos/delete' => ['controller' => 'DerechoController', 'method' => 'delete'],
    '/sismin/derechos/search' => ['controller' => 'DerechoController', 'method' => 'search'],

    // Categorías
    '/sismin/categorias' => ['controller' => 'CategoriaController', 'method' => 'index'],
    '/sismin/categorias/create' => ['controller' => 'CategoriaController', 'method' => 'create'],
    '/sismin/categorias/edit' => ['controller' => 'CategoriaController', 'method' => 'edit'],
    '/sismin/categorias/delete' => ['controller' => 'CategoriaController', 'method' => 'delete'],

    // Configuraciones
    '/sismin/configuraciones' => ['controller' => 'ConfiguracionController', 'method' => 'index'],
    '/sismin/configuraciones/municipio' => ['controller' => 'ConfiguracionController', 'method' => 'municipio'],
    '/sismin/configuraciones/municipio/update' => ['controller' => 'ConfiguracionController', 'method' => 'updateMunicipio'],

    // UMA
    '/sismin/uma' => ['controller' => 'UMAController', 'method' => 'index'],
    '/sismin/uma/create' => ['controller' => 'UMAController', 'method' => 'create'],
    '/sismin/uma/edit' => ['controller' => 'UMAController', 'method' => 'edit'],
    '/sismin/uma/delete' => ['controller' => 'UMAController', 'method' => 'delete'],

    // Usuarios
    '/sismin/usuarios' => ['controller' => 'UsuarioController', 'method' => 'index'],
    '/sismin/usuarios/create' => ['controller' => 'UsuarioController', 'method' => 'create'],
    '/sismin/usuarios/store' => ['controller' => 'UsuarioController', 'method' => 'store'],
    '/sismin/usuarios/edit' => ['controller' => 'UsuarioController', 'method' => 'edit'],
    '/sismin/usuarios/update' => ['controller' => 'UsuarioController', 'method' => 'update'],
];