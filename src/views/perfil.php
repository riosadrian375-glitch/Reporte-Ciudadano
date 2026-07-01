<section class="page-header">
    <div class="container">
        <h1 class="page-title">Mi Perfil</h1>
    </div>
</section>
<section class="page-content">
    <div class="container">
        <?php $usuario = Usuario::porId($_SESSION['usuario_id']); ?>
        <div class="perfil-layout">
            <div class="perfil-card card">
                <div class="perfil-avatar">
                    <span class="avatar-large"><?= strtoupper(substr($usuario['nombre'], 0, 1)) ?></span>
                </div>
                <h2 class="perfil-nombre"><?= htmlspecialchars($usuario['nombre']) ?></h2>
                <p class="perfil-correo"><?= htmlspecialchars($usuario['correo']) ?></p>
                <div class="perfil-info">
                    <div class="perfil-info-item">
                        <span class="perfil-info-label">Distrito</span>
                        <span class="perfil-info-value"><?= htmlspecialchars($usuario['distrito_nombre'] ?? 'No especificado') ?></span>
                    </div>
                    <div class="perfil-info-item">
                        <span class="perfil-info-label">Rol</span>
                        <span class="perfil-info-value"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $usuario['rol']))) ?></span>
                    </div>
                    <div class="perfil-info-item">
                        <span class="perfil-info-label">Miembro desde</span>
                        <span class="perfil-info-value"><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></span>
                    </div>
                </div>
                <?php
                $misReportes = Reporte::listar(['usuario_id' => $_SESSION['usuario_id']]);
                $totalLikes = 0;
                foreach ($misReportes as $r) { $totalLikes += (int)$r['total_likes']; }
                ?>
                <div class="perfil-stats">
                    <div class="perfil-stat"><span class="stat-number"><?= count($misReportes) ?></span><span class="stat-label">Reportes</span></div>
                    <div class="perfil-stat"><span class="stat-number"><?= $totalLikes ?></span><span class="stat-label">Likes recibidos</span></div>
                </div>
                <a href="<?= SITE_URL ?>/index.php?view=mis-reportes" class="btn btn-outline btn-full">Mis Reportes</a>
            </div>
        </div>
    </div>
</section>
