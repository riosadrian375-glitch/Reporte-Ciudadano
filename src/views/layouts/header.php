<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> — <?= SITE_DESCRIPTION ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/dashboards.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/arequipa-theme.css?v=arequipa-pro-4">
    <link rel="icon" type="image/svg+xml" href="<?= SITE_URL ?>/assets/img/favicon.svg">
</head>
<body>
    <nav class="nav-principal">
        <div class="container nav-container">
            <a href="<?= SITE_URL ?>/index.php?view=<?= htmlspecialchars(vistaPrincipalPorRol()) ?>" class="nav-logo">
                <div class="brand-seal">
                    <img src="<?= SITE_URL ?>/assets/img/escudo-arequipa.png" alt="Escudo de Arequipa">
                </div>
                <span class="brand-copy">
                    <span class="logo-text">Reporte<span class="logo-highlight">Ciudadano</span></span>
                    <small>Arequipa participa</small>
                </span>
            </a>

            <span class="arequipa-flag-pill" title="Bandera de Arequipa">
                <img src="<?= SITE_URL ?>/assets/img/bandera-arequipa.png" alt="">
                <span>Arequipa</span>
            </span>

            <?php if (estaLogueado()): ?>
            <div class="nav-search">
                <form action="<?= SITE_URL ?>/index.php" method="GET" class="search-form" id="searchForm">
                    <input type="hidden" name="view" value="feed">
                    <input type="text" name="buscar" class="search-input" placeholder="Buscar reportes..." value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
                    <button type="submit" class="search-btn" aria-label="Buscar">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6E6E6E" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <div class="nav-actions">
                <?php if (estaLogueado()): ?>
                    <button class="btn-icon" id="themeToggle" aria-label="Cambiar modo oscuro/claro">
                        <svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                        <svg class="icon-moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                    </button>

                    <div class="nav-notifications">
                        <button class="btn-icon" id="notifBtn" aria-label="Notificaciones">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                            <span class="notif-badge" id="notifCount" style="display:none">0</span>
                        </button>
                        <div class="notif-dropdown" id="notifDropdown">
                            <div class="notif-header">
                                <h4>Notificaciones</h4>
                                <button id="marcarLeidas" class="btn-link">Marcar todas como leidas</button>
                            </div>
                            <div class="notif-list" id="notifList">
                                <p class="notif-empty">Cargando...</p>
                            </div>
                        </div>
                    </div>

                    <div class="nav-user">
                        <button class="btn-user" id="userMenuBtn">
                            <span class="user-avatar"><?= strtoupper(substr($_SESSION['usuario_nombre'] ?? 'U', 0, 1)) ?></span>
                            <span class="user-name"><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? '') ?></span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div class="user-dropdown" id="userDropdown">
                            <a href="<?= SITE_URL ?>/index.php?view=perfil" class="dropdown-item">Mi Perfil</a>
                            <a href="<?= SITE_URL ?>/index.php?view=mis-reportes" class="dropdown-item">Mis Reportes</a>
                            <a href="<?= SITE_URL ?>/index.php?view=guardados" class="dropdown-item">Guardados</a>
                            <hr class="dropdown-divider">
                            <?php if (esRol('admin_municipal')): ?>
                            <a href="<?= SITE_URL ?>/index.php?view=admin_dashboard" class="dropdown-item">Panel Municipal</a>
                            <?php endif; ?>
                            <?php if (esRol('operador')): ?>
                            <a href="<?= SITE_URL ?>/index.php?view=operador_dashboard" class="dropdown-item">Panel Operador</a>
                            <?php endif; ?>
                            <?php if (esRol('admin_sistema')): ?>
                            <a href="<?= SITE_URL ?>/index.php?view=sistema_dashboard" class="dropdown-item">Panel Sistema</a>
                            <?php endif; ?>
                            <hr class="dropdown-divider">
                            <a href="<?= SITE_URL ?>/index.php?view=logout" class="dropdown-item dropdown-danger">Cerrar Sesion</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/index.php?view=login" class="btn btn-outline">Iniciar Sesion</a>
                    <a href="<?= SITE_URL ?>/index.php?view=register" class="btn btn-primary">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="main-content">
