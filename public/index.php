<?php

require_once dirname(__DIR__) . '/config/app.php';

$view = isset($_GET['view']) ? preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $_GET['view']) : 'feed';

$publicRoutes = ['login', 'register'];
$allowedViews = [
    'feed', 'reporte/detalle', 'reporte/crear', 'reporte/editar',
    'mapa', 'emergencias',
    'admin/dashboard', 'admin/asignar', 'admin/reportes', 'admin/alertas',
    'operador/dashboard', 'operador/mis-reportes',
    'sistema/panel', 'sistema/usuarios',
    'admin_dashboard', 'operador_dashboard', 'ciudadano_dashboard', 'sistema_dashboard',
    'notificaciones', 'perfil', 'guardados', 'mis-reportes',
];

// Rutas públicas (login y register)
if (in_array($view, $publicRoutes)) {
    $file = dirname(__DIR__) . '/src/views/' . $view . '.php';
    if (file_exists($file)) {
        require_once $file;
    } else {
        require_once dirname(__DIR__) . '/src/views/login.php';
    }
    exit;
}

// API va ANTES de verificar login, porque tiene sus propias validaciones internas
if ($view === 'api') {
    require_once dirname(__DIR__) . '/src/controllers/api.php';
    exit;
}

// A partir de aquí sí se requiere login
redirigirSiNoLogueado();

if ($view === 'logout') {
    session_destroy();
    header('Location: ' . SITE_URL . '/index.php?view=login');
    exit;
}

$rolesPorVista = [
    'admin/dashboard' => 'admin_municipal',
    'admin_dashboard' => 'admin_municipal',
    'operador/dashboard' => 'operador',
    'operador_dashboard' => 'operador',
    'ciudadano_dashboard' => 'ciudadano',
    'sistema/panel' => 'admin_sistema',
    'sistema_dashboard' => 'admin_sistema',
];
if (isset($rolesPorVista[$view]) && !esRol($rolesPorVista[$view])) {
    header('Location: ' . SITE_URL . '/index.php?view=' . vistaPrincipalPorRol());
    exit;
}

$viewFile = dirname(__DIR__) . '/src/views/' . $view . '.php';
if (!file_exists($viewFile) && !in_array($view, $allowedViews)) {
    $view = 'feed';
    $viewFile = dirname(__DIR__) . '/src/views/feed.php';
}

require_once dirname(__DIR__) . '/src/views/layouts/header.php';

if (file_exists($viewFile)) {
    require_once $viewFile;
} else {
    echo '<main class="container"><p>Vista no encontrada.</p></main>';
}

require_once dirname(__DIR__) . '/src/views/layouts/footer.php';
