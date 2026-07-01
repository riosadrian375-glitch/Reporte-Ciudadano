<section class="page-header">
    <div class="container">
        <h1 class="page-title">Mis Reportes</h1>
        <p class="page-subtitle">Reportes que has creado</p>
        <a href="<?= SITE_URL ?>/index.php?view=reporte/crear" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nuevo Reporte
        </a>
    </div>
</section>
<section class="page-content">
    <div class="container">
        <?php $reportes = Reporte::listar(['usuario_id' => $_SESSION['usuario_id']]); ?>
        <?php if (!empty($reportes)): ?>
        <div class="card-grid">
            <?php foreach ($reportes as $reporte): ?>
                <?php require __DIR__ . '/partials/tarjeta-reporte.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#9E9E9E" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            <h3>No has creado reportes aun</h3>
            <p>Se el primero en reportar algo en tu comunidad.</p>
            <a href="<?= SITE_URL ?>/index.php?view=reporte/crear" class="btn btn-primary">Crear Reporte</a>
        </div>
        <?php endif; ?>
    </div>
</section>
