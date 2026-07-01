<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/arequipa-theme.css?v=arequipa-pro-4">
    <link rel="icon" type="image/svg+xml" href="<?= SITE_URL ?>/assets/img/favicon.svg">
</head>
<body class="auth-body arequipa-auth">
    <main class="auth-shell">
        <section class="auth-visual" aria-label="ReporteCiudadano Arequipa">
            <div class="auth-visual-overlay"></div>
            <div class="auth-visual-content">
                <span class="auth-kicker">CIUDAD DEL MISTI</span>
                <h2>Arequipa reporta.<br>Arequipa mejora.</h2>
                <p>Una comunidad conectada para transformar cada incidencia en una acción visible.</p>
                <div class="auth-city-facts">
                    <span><strong>21</strong> distritos conectados</span>
                    <span><strong>24/7</strong> participación activa</span>
                </div>
            </div>
            <figure class="auth-flag-card">
                <img src="<?= SITE_URL ?>/assets/img/bandera-arequipa.png" alt="Bandera de Arequipa">
                <figcaption>Identidad arequipeña</figcaption>
            </figure>
        </section>

        <section class="auth-container">
            <a href="<?= SITE_URL ?>/index.php?view=feed" class="auth-logo">
                <div class="auth-brand-seal"><img src="<?= SITE_URL ?>/assets/img/escudo-arequipa.png" alt="Escudo de Arequipa"></div>
                <span class="brand-copy"><span class="logo-text">Reporte<span class="logo-highlight">Ciudadano</span></span><small>Arequipa participa</small></span>
            </a>
            <span class="auth-form-kicker">BIENVENIDO DE NUEVO</span>
            <h1 class="auth-title">Iniciar sesión</h1>
            <p class="auth-subtitle">Accede a tu panel y sigue mejorando tu ciudad.</p>

            <form id="loginForm" class="auth-form">
                <div class="form-group">
                    <label for="correo">Correo electrónico</label>
                    <input type="email" id="correo" name="correo" class="form-input" placeholder="tu@correo.com" required autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required autocomplete="current-password">
                </div>
                <div class="form-error" id="loginError" style="display:none"></div>
                <button type="submit" class="btn btn-primary btn-full">Ingresar a ReporteCiudadano</button>
            </form>

            <p class="auth-footer">¿No tienes cuenta? <a href="<?= SITE_URL ?>/index.php?view=register">Regístrate</a></p>

            <details class="auth-dev-note">
                <summary>Ver cuentas de demostración</summary>
                <div class="auth-demo-grid">
                    <span><b>Ciudadano</b> carlos@example.com</span>
                    <span><b>Municipal</b> admin@municipal.gob.pe</span>
                    <span><b>Operador</b> operador1@municipal.gob.pe</span>
                    <span><b>Sistema</b> sysadmin@reporteciudadano.pe</span>
                </div>
                <small>Contraseña para todas: <strong>test1234</strong></small>
            </details>
        </section>
    </main>

    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
