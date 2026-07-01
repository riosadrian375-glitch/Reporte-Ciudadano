<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse — <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/arequipa-theme.css?v=arequipa-pro-4">
    <link rel="icon" type="image/svg+xml" href="<?= SITE_URL ?>/assets/img/favicon.svg">
</head>
<body class="auth-body arequipa-auth">
    <main class="auth-shell auth-shell-register">
        <section class="auth-visual" aria-label="ReporteCiudadano Arequipa">
            <div class="auth-visual-overlay"></div>
            <div class="auth-visual-content">
                <span class="auth-kicker">ORGULLO AREQUIPEÑO</span>
                <h2>Tu voz también<br>construye ciudad.</h2>
                <p>Reporta, comparte y acompaña las mejoras urbanas desde cualquier distrito.</p>
                <div class="auth-city-facts">
                    <span><strong>1</strong> reporte puede cambiar una calle</span>
                    <span><strong>4</strong> actores trabajando juntos</span>
                </div>
            </div>
            <figure class="auth-flag-card">
                <img src="<?= SITE_URL ?>/assets/img/bandera-arequipa.png" alt="Bandera de Arequipa">
                <figcaption>Ciudad del Misti</figcaption>
            </figure>
        </section>

        <section class="auth-container">
            <a href="<?= SITE_URL ?>/index.php?view=feed" class="auth-logo">
                <div class="auth-brand-seal"><img src="<?= SITE_URL ?>/assets/img/escudo-arequipa.png" alt="Escudo de Arequipa"></div>
                <span class="brand-copy"><span class="logo-text">Reporte<span class="logo-highlight">Ciudadano</span></span><small>Arequipa participa</small></span>
            </a>
            <span class="auth-form-kicker">ÚNETE A LA COMUNIDAD</span>
            <h1 class="auth-title">Crear cuenta</h1>
            <p class="auth-subtitle">Regístrate y ayuda a mejorar Arequipa.</p>

            <form id="registerForm" class="auth-form">
                <div class="form-group">
                    <label for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" class="form-input" placeholder="Tu nombre" required autocomplete="name">
                </div>
                <div class="form-group">
                    <label for="correo">Correo electrónico</label>
                    <input type="email" id="correo" name="correo" class="form-input" placeholder="tu@correo.com" required autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="distrito_id">Distrito de Arequipa</label>
                    <select id="distrito_id" name="distrito_id" class="form-input" required>
                        <option value="">Selecciona tu distrito</option>
                        <?php $distritos = Distrito::listar(); foreach ($distritos as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="Mínimo 6 caracteres" required minlength="6" autocomplete="new-password">
                </div>
                <div class="form-error" id="registerError" style="display:none"></div>
                <button type="submit" class="btn btn-primary btn-full">Crear mi cuenta</button>
            </form>

            <p class="auth-footer">¿Ya tienes cuenta? <a href="<?= SITE_URL ?>/index.php?view=login">Inicia sesión</a></p>
        </section>
    </main>

    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
