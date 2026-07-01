<?php
$iconoReporte = trim((string)($reporte['categoria_icono'] ?? ''));
$mostrarIconoReporte = $iconoReporte !== '' && !preg_match('/^[a-zA-Z0-9_ -]+$/u', $iconoReporte);
?>

<div class="card reporte-card" data-reporte-id="<?= $reporte['id'] ?>">
    <div class="card-image">
        <a href="<?= SITE_URL ?>/index.php?view=reporte/detalle&id=<?= $reporte['id'] ?>">
            <?php if (!empty($reporte['imagen_thumbnail'])): ?>
                <img src="<?= SITE_URL ?>/<?= htmlspecialchars($reporte['imagen_thumbnail']) ?>" alt="<?= htmlspecialchars($reporte['titulo']) ?>" loading="lazy">
            <?php else: ?>
                <div class="card-image-placeholder">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/><path d="M21 15l-5-5L5 21"/>
                    </svg>
                </div>
            <?php endif; ?>
        </a>
        <div class="card-badges">
            <span class="chip chip-categoria">
                <?php if ($mostrarIconoReporte): ?>
                    <span class="chip-icon"><?= htmlspecialchars($iconoReporte) ?></span>
                <?php endif; ?>
                <?= htmlspecialchars($reporte['categoria_nombre'] ?? '') ?>
            </span>
            <?php if (!empty($reporte['es_urgente'])): ?>
                <span class="chip chip-urgente">Urgente</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="card-meta">
            <span class="card-distrito"><?= htmlspecialchars($reporte['distrito_nombre'] ?? '') ?></span>
            <span class="card-date"><?= tiempoRelativo($reporte['fecha_creacion'] ?? '') ?></span>
        </div>
        <h3 class="card-title">
            <a href="<?= SITE_URL ?>/index.php?view=reporte/detalle&id=<?= $reporte['id'] ?>"><?= htmlspecialchars($reporte['titulo'] ?? '') ?></a>
        </h3>
        <p class="card-desc"><?= htmlspecialchars(resumirTexto($reporte['descripcion'] ?? '', 120)) ?></p>
        <div class="card-footer">
            <div class="card-actions">
                <button class="btn-action btn-like" data-action="like" data-reporte-id="<?= $reporte['id'] ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                    <span class="count like-count"><?= (int)($reporte['total_likes'] ?? 0) ?></span>
                </button>
                <button class="btn-action btn-save" data-action="save" data-reporte-id="<?= $reporte['id'] ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
                </button>
                <button class="btn-action btn-share" data-action="share" data-reporte-id="<?= $reporte['id'] ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/></svg>
                    <span class="count"><?= (int)($reporte['total_compartidos'] ?? 0) ?></span>
                </button>
                <a href="<?= SITE_URL ?>/index.php?view=reporte/detalle&id=<?= $reporte['id'] ?>#comentarios" class="btn-action">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                    <span class="count"><?= (int)($reporte['total_comentarios'] ?? 0) ?></span>
                </a>
            </div>
            <div class="card-status">
                <span class="status-badge status-<?= htmlspecialchars($reporte['estado'] ?? 'pendiente') ?>">
                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $reporte['estado'] ?? 'pendiente'))) ?>
                </span>
            </div>
        </div>
        <button class="btn-ia-card" data-action="ia-ask" data-reporte-id="<?= $reporte['id'] ?>">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a4 4 0 014 4c0 2-2 4-4 4s-4-2-4-4 2-4 4-4z"/><path d="M2 22v-2a6 6 0 016-6h8a6 6 0 016 6v2"/></svg>
            Preguntar a la IA
        </button>
    </div>
</div>
