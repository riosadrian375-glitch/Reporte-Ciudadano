<section class="page-header">
    <div class="container">
        <h1 class="page-title">Reportes Guardados</h1>
        <p class="page-subtitle">Reportes que has guardado para ver despues</p>
    </div>
</section>
<section class="page-content">
    <div class="container">
        <?php
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("
            SELECT r.*, c.nombre as categoria_nombre, c.icono as categoria_icono,
                   d.nombre as distrito_nombre, u.nombre as usuario_nombre,
                   (SELECT COUNT(*) FROM likes WHERE reporte_id = r.id) as total_likes,
                   (SELECT COUNT(*) FROM comentarios WHERE reporte_id = r.id) as total_comentarios,
                   (SELECT COUNT(*) FROM compartidos WHERE reporte_id = r.id) as total_compartidos,
                   (SELECT ri.ruta_archivo FROM reporte_imagenes ri WHERE ri.reporte_id = r.id LIMIT 1) as imagen_thumbnail,
                   g.fecha as fecha_guardado
            FROM guardados g
            JOIN reportes r ON g.reporte_id = r.id
            JOIN categorias c ON r.categoria_id = c.id
            JOIN distritos d ON r.distrito_id = d.id
            JOIN usuarios u ON r.usuario_id = u.id
            WHERE g.usuario_id = ?
            ORDER BY g.fecha DESC
        ");
        $stmt->execute([$_SESSION['usuario_id']]);
        $reportes = $stmt->fetchAll();
        ?>
        <?php if (!empty($reportes)): ?>
        <div class="card-grid">
            <?php foreach ($reportes as $reporte): ?>
                <?php require __DIR__ . '/partials/tarjeta-reporte.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#9E9E9E" stroke-width="1.5"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
            <h3>No tienes reportes guardados</h3>
            <p>Guarda reportes para verlos despues facilmente.</p>
            <a href="<?= SITE_URL ?>/index.php?view=feed" class="btn btn-primary">Explorar reportes</a>
        </div>
        <?php endif; ?>
    </div>
</section>
