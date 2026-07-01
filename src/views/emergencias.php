<section class="page-header">
    <div class="container">
        <h1 class="page-title">Contactos de Emergencia</h1>
        <p class="page-subtitle">Numeros importantes para situaciones de emergencia en Arequipa</p>
    </div>
</section>
<section class="page-content">
    <div class="container">
        <?php $contactos = EmergenciaContacto::listar(); ?>
        <?php if (!empty($contactos)): ?>
        <div class="emergencias-grid">
            <?php foreach ($contactos as $c): ?>
            <div class="card emergencia-card">
                <div class="emergencia-icon">
                    <?php if (!empty($c['icono'])): ?>
                    <img src="<?= htmlspecialchars($c['icono']) ?>" alt="<?= htmlspecialchars($c['nombre']) ?>" width="48" height="48">
                    <?php else: ?>
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--acento-rojo-institucional)" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
                    <?php endif; ?>
                </div>
                <div class="emergencia-info">
                    <h3><?= htmlspecialchars($c['nombre']) ?></h3>
                    <p class="emergencia-desc"><?= htmlspecialchars($c['descripcion'] ?? '') ?></p>
                    <a href="tel:<?= htmlspecialchars($c['numero']) ?>" class="btn btn-primary emergencia-llamar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
                        <?= htmlspecialchars($c['numero']) ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#9E9E9E" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
            <h3>No hay contactos de emergencia configurados</h3>
            <p>Contacta al administrador del sistema para agregar numeros de emergencia.</p>
        </div>
        <?php endif; ?>
        <div class="emergencias-nota">
            <p><strong>Nota:</strong> Estos numeros son proporcionados por la municipalidad de Arequipa. En caso de emergencia grave, llama siempre al <a href="tel:105">105</a> (Policia) o <a href="tel:116">116</a> (Bomberos).</p>
        </div>
    </div>
</section>
