<?php
if (!esRol('operador')) {
    http_response_code(403);
    echo '<section class="rc-dashboard"><div class="rc-empty">Acceso exclusivo para operadores técnicos.</div></section>';
    return;
}

$asignacionesOperador = array_values(array_filter(
    Asignacion::porOperador($_SESSION['usuario_id']),
    function ($asignacion) { return ($asignacion['reporte_estado'] ?? '') === 'en_proceso'; }
));

function rcOperadorClima($valor) {
    if (!$valor) return 'Sin registro';
    $clima = is_array($valor) ? $valor : json_decode($valor, true);
    if (!is_array($clima)) return 'Sin registro';
    $texto = ucfirst((string)($clima['descripcion'] ?? 'Sin descripción'));
    if (isset($clima['temperatura'])) $texto .= ' · ' . $clima['temperatura'] . ' °C';
    return $texto;
}

function rcOperadorArchivo($ruta) {
    if (!$ruta) return '';
    if (preg_match('/^https?:\/\//i', $ruta)) return $ruta;
    return SITE_URL . '/' . ltrim($ruta, '/');
}

$tareasOperadorJs = [];
foreach ($asignacionesOperador as $tareaOperador) {
    $tareasOperadorJs[(string)$tareaOperador['reporte_id']] = [
        'id' => (int)$tareaOperador['reporte_id'],
        'titulo' => $tareaOperador['titulo'],
        'descripcion' => $tareaOperador['descripcion'],
        'categoria' => $tareaOperador['categoria_nombre'],
        'distrito' => $tareaOperador['distrito_nombre'],
        'direccion' => $tareaOperador['direccion'] ?: 'Dirección no especificada',
        'clima' => rcOperadorClima($tareaOperador['clima_momento'] ?? null),
        'latitud' => $tareaOperador['latitud'],
        'longitud' => $tareaOperador['longitud'],
    ];
}
$primeraTareaOperador = $asignacionesOperador ? reset($asignacionesOperador) : null;
?>

<section class="rc-dashboard rc-operator-dashboard">
    <div class="rc-shell">
        <header class="rc-page-heading">
            <div>
                <span class="rc-eyebrow">OPERACIÓN TÉCNICA · CUS-08</span>
                <h1>Mis tareas activas</h1>
                <p>Atiende reportes asignados y documenta el cierre con evidencia.</p>
            </div>
            <span class="rc-counter"><?= count($asignacionesOperador) ?> en proceso</span>
        </header>

        <div class="rc-operator-grid">
            <section class="rc-task-list" aria-labelledby="tasks-title">
                <div class="rc-section-heading">
                    <span class="rc-label">LISTA DE TAREAS</span>
                    <h2 id="tasks-title">Asignaciones en proceso</h2>
                </div>

                <?php if ($asignacionesOperador): ?>
                    <?php foreach ($asignacionesOperador as $indiceTarea => $tareaOperador): ?>
                        <article class="rc-task-card <?= $indiceTarea === 0 ? 'is-selected' : '' ?>" data-report-id="<?= (int)$tareaOperador['reporte_id'] ?>">
                            <?php if (!empty($tareaOperador['imagen_thumbnail'])): ?>
                                <img src="<?= htmlspecialchars(rcOperadorArchivo($tareaOperador['imagen_thumbnail'])) ?>" alt="Evidencia del reporte" loading="lazy">
                            <?php elseif (!empty($tareaOperador['video_thumbnail'])): ?>
                                <video src="<?= htmlspecialchars(rcOperadorArchivo($tareaOperador['video_thumbnail'])) ?>" preload="metadata" muted></video>
                            <?php else: ?>
                                <div class="rc-media-placeholder" aria-hidden="true">RC</div>
                            <?php endif; ?>
                            <div class="rc-task-body">
                                <div class="rc-task-meta">
                                    <span class="rc-category"><?= htmlspecialchars(mb_strtoupper($tareaOperador['categoria_nombre'])) ?></span>
                                    <span class="rc-status rc-status-en_proceso">EN PROCESO</span>
                                </div>
                                <h3><?= htmlspecialchars($tareaOperador['titulo']) ?></h3>
                                <p><?= htmlspecialchars(resumirTexto($tareaOperador['descripcion'], 135)) ?></p>
                                <div class="rc-task-facts">
                                    <span><?= htmlspecialchars($tareaOperador['distrito_nombre']) ?></span>
                                    <span><?= htmlspecialchars(rcOperadorClima($tareaOperador['clima_momento'] ?? null)) ?></span>
                                </div>
                                <button type="button" class="rc-btn rc-btn-secondary rc-open-task" data-report-id="<?= (int)$tareaOperador['reporte_id'] ?>">Abrir asignación</button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="rc-empty">
                        <strong>No tienes reportes en proceso.</strong>
                        <span>Las nuevas asignaciones aparecerán aquí automáticamente.</span>
                    </div>
                <?php endif; ?>
            </section>

            <aside class="rc-panel rc-resolution-panel" aria-labelledby="resolution-title">
                <div class="rc-panel-heading">
                    <div>
                        <span class="rc-label">ACTUALIZACIÓN DE ASIGNACIÓN</span>
                        <h2 id="resolution-title"><?= $primeraTareaOperador ? htmlspecialchars($primeraTareaOperador['titulo']) : 'Sin tarea seleccionada' ?></h2>
                    </div>
                </div>

                <div class="rc-resolution-context">
                    <p id="rcOperatorDescription"><?= $primeraTareaOperador ? htmlspecialchars($primeraTareaOperador['descripcion']) : 'Selecciona una tarea para ver sus detalles.' ?></p>
                    <dl class="rc-detail-list">
                        <div><dt>DISTRITO</dt><dd id="rcOperatorDistrict"><?= $primeraTareaOperador ? htmlspecialchars($primeraTareaOperador['distrito_nombre']) : '—' ?></dd></div>
                        <div><dt>DIRECCIÓN</dt><dd id="rcOperatorAddress"><?= $primeraTareaOperador ? htmlspecialchars($primeraTareaOperador['direccion'] ?: 'Dirección no especificada') : '—' ?></dd></div>
                        <div><dt>CLIMA DEL REPORTE</dt><dd id="rcOperatorWeather"><?= $primeraTareaOperador ? htmlspecialchars(rcOperadorClima($primeraTareaOperador['clima_momento'] ?? null)) : '—' ?></dd></div>
                    </dl>
                    <a id="rcOperatorMapLink" class="rc-inline-link" target="_blank" rel="noopener" <?= $primeraTareaOperador && $primeraTareaOperador['latitud'] && $primeraTareaOperador['longitud'] ? '' : 'hidden' ?> href="<?= $primeraTareaOperador && $primeraTareaOperador['latitud'] && $primeraTareaOperador['longitud'] ? 'https://www.openstreetmap.org/?mlat=' . rawurlencode($primeraTareaOperador['latitud']) . '&mlon=' . rawurlencode($primeraTareaOperador['longitud']) . '#map=17/' . rawurlencode($primeraTareaOperador['latitud']) . '/' . rawurlencode($primeraTareaOperador['longitud']) : '#' ?>">Ver ubicación en el mapa</a>
                </div>

                <form id="rcResolutionForm" class="rc-stack-form" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="reporte_id" id="rcOperatorReportId" value="<?= $primeraTareaOperador ? (int)$primeraTareaOperador['reporte_id'] : '' ?>">
                    <fieldset class="rc-segmented-field">
                        <legend>ESTADO DE LA ATENCIÓN</legend>
                        <div class="rc-segmented">
                            <label><input type="radio" name="estado" value="en_proceso" checked><span>EN PROCESO</span></label>
                            <label><input type="radio" name="estado" value="resuelto"><span>RESUELTO</span></label>
                        </div>
                    </fieldset>
                    <label>
                        <span>COMENTARIO DE RESOLUCIÓN · VALIDACIÓN DE IA</span>
                        <textarea name="comentario_resolucion" rows="5" required maxlength="1200" placeholder="Describe el trabajo realizado, el resultado y cualquier observación técnica."></textarea>
                    </label>
                    <label class="rc-file-field">
                        <span>EVIDENCIA VISUAL DE CIERRE</span>
                        <input type="file" name="evidencia" required accept="image/jpeg,image/png,image/webp">
                        <small>JPG, PNG o WEBP · máximo 5 MB</small>
                    </label>
                    <div class="rc-form-message" id="rcResolutionMessage" role="status"></div>
                    <button type="submit" class="rc-btn rc-btn-primary" <?= !$primeraTareaOperador ? 'disabled' : '' ?>>Confirmar actualización</button>
                </form>
            </aside>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tasks = <?= json_encode($tareasOperadorJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const form = document.getElementById('rcResolutionForm');
    const message = document.getElementById('rcResolutionMessage');

    function openTask(id) {
        const task = tasks[String(id)];
        if (!task) return;
        document.querySelectorAll('.rc-task-card').forEach(function (card) {
            card.classList.toggle('is-selected', card.dataset.reportId === String(id));
        });
        document.getElementById('rcOperatorReportId').value = task.id;
        document.getElementById('resolution-title').textContent = task.titulo;
        document.getElementById('rcOperatorDescription').textContent = task.descripcion;
        document.getElementById('rcOperatorDistrict').textContent = task.distrito;
        document.getElementById('rcOperatorAddress').textContent = task.direccion;
        document.getElementById('rcOperatorWeather').textContent = task.clima;
        const mapLink = document.getElementById('rcOperatorMapLink');
        if (task.latitud && task.longitud) {
            mapLink.href = 'https://www.openstreetmap.org/?mlat=' + encodeURIComponent(task.latitud) + '&mlon=' + encodeURIComponent(task.longitud) + '#map=17/' + encodeURIComponent(task.latitud) + '/' + encodeURIComponent(task.longitud);
            mapLink.hidden = false;
        } else {
            mapLink.hidden = true;
        }
        form.querySelector('button[type="submit"]').disabled = false;
        message.textContent = '';
    }

    document.querySelectorAll('.rc-open-task').forEach(function (button) {
        button.addEventListener('click', function () { openTask(this.dataset.reportId); });
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = 'Guardando…';
        message.textContent = '';
        try {
            const response = await fetch('<?= SITE_URL ?>/index.php?view=api&action=actualizar_estado_reporte', { method: 'POST', body: new FormData(form) });
            const data = await response.json();
            if (!data.success) throw new Error(data.error || 'No se pudo actualizar el reporte.');
            message.textContent = data.message;
            message.className = 'rc-form-message is-success';
            window.setTimeout(function () { window.location.reload(); }, 800);
        } catch (error) {
            message.textContent = error.message;
            message.className = 'rc-form-message is-error';
            button.disabled = false;
            button.textContent = 'Confirmar actualización';
        }
    });
});
</script>
