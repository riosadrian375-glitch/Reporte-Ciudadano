<section class="page-header">
    <div class="container">
        <h1 class="page-title">Mis Reportes Asignados</h1>
        <p class="page-subtitle">Historial de reportes que has atendido</p>
    </div>
</section>
<section class="page-content">
    <div class="container">
        <?php
        $asignaciones = Asignacion::porOperador($_SESSION['usuario_id']);
        ?>
        <?php if (empty($asignaciones)): ?>
        <div class="empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#9E9E9E" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            <h3>No tienes reportes asignados</h3>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr><th>ID</th><th>Titulo</th><th>Categoria</th><th>Distrito</th><th>Ciudadano</th><th>Estado</th><th>Asignado</th><th>Accion</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($asignaciones as $a): ?>
                    <tr>
                        <td><?= $a['reporte_id'] ?></td>
                        <td><a href="<?= SITE_URL ?>/index.php?view=reporte/detalle&id=<?= $a['reporte_id'] ?>"><?= htmlspecialchars($a['titulo']) ?></a></td>
                        <td><?= htmlspecialchars($a['categoria_nombre'] ?? '') ?></td>
                        <td><?= htmlspecialchars($a['distrito_nombre'] ?? '') ?></td>
                        <td><?= htmlspecialchars($a['ciudadano_nombre'] ?? '') ?></td>
                        <td><span class="status-badge status-<?= $a['reporte_estado'] ?>"><?= ucfirst(str_replace('_', ' ', $a['reporte_estado'])) ?></span></td>
                        <td><?= tiempoRelativo($a['fecha_asignacion']) ?></td>
                        <td>
                            <a href="<?= SITE_URL ?>/index.php?view=reporte/detalle&id=<?= $a['reporte_id'] ?>" class="btn btn-outline btn-sm">Ver</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</section>
