<section class="page-header">
    <div class="container">
        <h1 class="page-title">Notificaciones</h1>
        <p class="page-subtitle">Todas tus notificaciones</p>
    </div>
</section>
<section class="page-content">
    <div class="container">
        <div class="notif-controls">
            <button class="btn btn-outline btn-sm" id="marcarTodasLeidasPg">Marcar todas como leidas</button>
        </div>
        <div class="notif-list-page" id="notifListPage">
            <?php
            $notificaciones = Notificacion::todas($_SESSION['usuario_id']);
            if (!empty($notificaciones)):
                foreach ($notificaciones as $n):
            ?>
            <div class="notif-item <?= $n['leida'] ? '' : 'notif-no-leida' ?>" data-id="<?= $n['id'] ?>">
                <div class="notif-item-icon">
                    <?php if ($n['tipo'] === 'comentario'): ?>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                    <?php elseif ($n['tipo'] === 'asignacion'): ?>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/></svg>
                    <?php elseif ($n['tipo'] === 'estado'): ?>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <?php else: ?>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                    <?php endif; ?>
                </div>
                <div class="notif-item-content">
                    <p><?= htmlspecialchars($n['mensaje']) ?></p>
                    <span class="notif-item-date"><?= tiempoRelativo($n['fecha']) ?></span>
                </div>
                <?php if (!empty($n['referencia_id'])): ?>
                <a href="<?= SITE_URL ?>/index.php?view=reporte/detalle&id=<?= $n['referencia_id'] ?>" class="btn btn-outline btn-sm">Ver reporte</a>
                <?php endif; ?>
            </div>
            <?php endforeach; else: ?>
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9E9E9E" stroke-width="1.5"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                <h3>No tienes notificaciones</h3>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<script>
document.getElementById("marcarTodasLeidasPg").addEventListener("click", function() {
    var fd = new FormData();
    fd.append("id", 0);
    fetch("<?= SITE_URL ?>/index.php?view=api&action=notificaciones_marcar", { method: "POST", body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                document.querySelectorAll(".notif-no-leida").forEach(function(el) { el.classList.remove("notif-no-leida"); });
            }
        });
});
</script>
