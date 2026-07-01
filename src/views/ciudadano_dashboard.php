<?php
if (!esRol('ciudadano')) {
    http_response_code(403);
    echo '<section class="rc-dashboard"><div class="rc-empty">Acceso exclusivo para ciudadanos.</div></section>';
    return;
}

$distritoCiudadanoId = (int)($_SESSION['usuario_distrito'] ?? 0);
$usuarioCiudadano = Usuario::porId($_SESSION['usuario_id']);
$reportesCiudadano = Reporte::listar([
    'priorizar_distrito' => $distritoCiudadanoId,
    'orden' => 'recientes',
]);

function rcCiudadanoArchivo($ruta) {
    if (!$ruta) return '';
    if (preg_match('/^https?:\/\//i', $ruta)) return $ruta;
    return SITE_URL . '/' . ltrim($ruta, '/');
}
?>

<section class="rc-dashboard rc-citizen-dashboard">
    <div class="rc-shell rc-feed-shell">
        <header class="rc-weather-bar" aria-label="Bienvenida y clima del distrito">
            <div>
                <span class="rc-eyebrow">TU COMUNIDAD · <?= htmlspecialchars(mb_strtoupper($usuarioCiudadano['distrito_nombre'] ?? 'AREQUIPA')) ?></span>
                <h1>Hola, <?= htmlspecialchars(explode(' ', trim($_SESSION['usuario_nombre'] ?? 'Ciudadano'))[0]) ?></h1>
                <p>Los reportes de tu distrito aparecen primero.</p>
            </div>
            <div class="rc-weather-live" id="rcCitizenWeather" data-district="<?= $distritoCiudadanoId ?>">
                <span class="rc-weather-icon" id="rcCitizenWeatherIcon" aria-hidden="true">○</span>
                <div><strong id="rcCitizenTemperature">-- °C</strong><span id="rcCitizenCondition">Consultando clima…</span></div>
                <div class="rc-weather-extra"><span>HUMEDAD <b id="rcCitizenHumidity">--%</b></span><span>VIENTO <b id="rcCitizenWind">-- km/h</b></span></div>
            </div>
        </header>

        <div class="rc-feed-heading">
            <div>
                <span class="rc-label">ACTIVIDAD URBANA</span>
                <h2>Reportes cerca de ti</h2>
            </div>
            <a class="rc-btn rc-btn-secondary" href="<?= SITE_URL ?>/index.php?view=mapa">Ver mapa general</a>
        </div>

        <div class="rc-social-feed">
            <?php if ($reportesCiudadano): ?>
                <?php foreach ($reportesCiudadano as $reporteCiudadano): ?>
                    <article class="rc-social-card" data-report-id="<?= (int)$reporteCiudadano['id'] ?>">
                        <header class="rc-social-card-header">
                            <div>
                                <span class="rc-category"><?= htmlspecialchars(mb_strtoupper($reporteCiudadano['categoria_nombre'])) ?></span>
                                <span><?= htmlspecialchars($reporteCiudadano['distrito_nombre']) ?> · <?= htmlspecialchars(tiempoRelativo($reporteCiudadano['fecha_creacion'])) ?></span>
                            </div>
                            <span class="rc-status rc-status-<?= htmlspecialchars($reporteCiudadano['estado']) ?>"><?= htmlspecialchars(mb_strtoupper(str_replace('_', ' ', $reporteCiudadano['estado']))) ?></span>
                        </header>

                        <div class="rc-social-media">
                            <?php if (!empty($reporteCiudadano['imagen_thumbnail'])): ?>
                                <img src="<?= htmlspecialchars(rcCiudadanoArchivo($reporteCiudadano['imagen_thumbnail'])) ?>" alt="Imagen del reporte <?= htmlspecialchars($reporteCiudadano['titulo']) ?>" loading="lazy">
                            <?php elseif (!empty($reporteCiudadano['video_thumbnail'])): ?>
                                <video src="<?= htmlspecialchars(rcCiudadanoArchivo($reporteCiudadano['video_thumbnail'])) ?>" controls preload="metadata"></video>
                            <?php else: ?>
                                <div class="rc-media-placeholder"><span>REPORTE CIUDADANO</span><strong><?= htmlspecialchars($reporteCiudadano['distrito_nombre']) ?></strong></div>
                            <?php endif; ?>
                        </div>

                        <div class="rc-social-body">
                            <h3><a href="<?= SITE_URL ?>/index.php?view=reporte/detalle&id=<?= (int)$reporteCiudadano['id'] ?>"><?= htmlspecialchars($reporteCiudadano['titulo']) ?></a></h3>
                            <p><?= htmlspecialchars(resumirTexto($reporteCiudadano['descripcion'], 220)) ?></p>

                            <?php if ($reporteCiudadano['latitud'] !== null && $reporteCiudadano['longitud'] !== null): ?>
                                <div class="rc-mini-map" id="rcMap<?= (int)$reporteCiudadano['id'] ?>" data-lat="<?= htmlspecialchars($reporteCiudadano['latitud']) ?>" data-lng="<?= htmlspecialchars($reporteCiudadano['longitud']) ?>" aria-label="Ubicación del reporte"></div>
                            <?php else: ?>
                                <div class="rc-map-unavailable">UBICACIÓN NO REGISTRADA</div>
                            <?php endif; ?>

                            <div class="rc-social-actions" aria-label="Interacciones del reporte">
                                <button type="button" data-social-action="like"><span>♡</span> Me gusta <b data-count="likes"><?= (int)$reporteCiudadano['total_likes'] ?></b></button>
                                <a href="<?= SITE_URL ?>/index.php?view=reporte/detalle&id=<?= (int)$reporteCiudadano['id'] ?>#comentarios"><span>○</span> Comentarios <b data-count="comments"><?= (int)$reporteCiudadano['total_comentarios'] ?></b></a>
                                <button type="button" data-social-action="share"><span>↗</span> Compartir <b data-count="shares"><?= (int)$reporteCiudadano['total_compartidos'] ?></b></button>
                                <button type="button" data-social-action="save"><span>◇</span> <i>Guardar</i></button>
                            </div>

                            <form class="rc-quick-comment" method="post">
                                <input type="hidden" name="reporte_id" value="<?= (int)$reporteCiudadano['id'] ?>">
                                <input type="text" name="contenido" required maxlength="500" autocomplete="off" placeholder="Añade una opinión · moderada por IA">
                                <button type="submit" aria-label="Publicar comentario">Enviar</button>
                                <span class="rc-inline-message" role="status"></span>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="rc-empty">
                    <strong>Aún no hay reportes publicados.</strong>
                    <span>Puedes iniciar la conversación urbana creando el primero.</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <a class="rc-create-report-fab" href="<?= SITE_URL ?>/index.php?view=reporte/crear">+ CREAR REPORTE</a>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const apiUrl = '<?= SITE_URL ?>/index.php?view=api&action=';
    const detailBase = '<?= SITE_URL ?>/index.php?view=reporte/detalle&id=';

    async function postAction(action, data) {
        const response = await fetch(apiUrl + action, { method: 'POST', body: data instanceof FormData ? data : new URLSearchParams(data) });
        const result = await response.json();
        if (!result.success) throw new Error(result.error || 'No se pudo completar la acción.');
        return result;
    }

    const district = document.getElementById('rcCitizenWeather').dataset.district;
    fetch(apiUrl + 'clima_obtener' + (district ? '&distrito_id=' + encodeURIComponent(district) : ''))
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (!data.success || !data.clima) throw new Error('Clima no disponible');
            const climate = data.clima;
            document.getElementById('rcCitizenTemperature').textContent = climate.temperatura + ' °C';
            document.getElementById('rcCitizenCondition').textContent = climate.descripcion || 'Arequipa';
            document.getElementById('rcCitizenHumidity').textContent = climate.humedad + '%';
            document.getElementById('rcCitizenWind').textContent = climate.viento + ' km/h';
            const icon = document.getElementById('rcCitizenWeatherIcon');
            if (climate.icono && !String(climate.icono).startsWith('http')) {
                icon.innerHTML = '<img src="https://openweathermap.org/img/wn/' + encodeURIComponent(climate.icono) + '@2x.png" alt="">';
            }
        })
        .catch(function () { document.getElementById('rcCitizenCondition').textContent = 'Clima temporalmente no disponible'; });

    if (window.L) {
        document.querySelectorAll('.rc-mini-map').forEach(function (element) {
            const lat = Number(element.dataset.lat);
            const lng = Number(element.dataset.lng);
            const map = L.map(element, { zoomControl: false, dragging: false, scrollWheelZoom: false, doubleClickZoom: false, boxZoom: false, keyboard: false, tap: false, attributionControl: false }).setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
            L.marker([lat, lng]).addTo(map);
        });
    }

    document.querySelectorAll('.rc-social-card').forEach(function (card) {
        card.addEventListener('click', async function (event) {
            const button = event.target.closest('[data-social-action]');
            if (!button) return;
            const id = card.dataset.reportId;
            const action = button.dataset.socialAction;
            button.disabled = true;
            try {
                if (action === 'like') {
                    const result = await postAction('likes_toggle', { reporte_id: id });
                    card.querySelector('[data-count="likes"]').textContent = result.total;
                    button.classList.toggle('is-active', result.action === 'liked');
                } else if (action === 'save') {
                    const result = await postAction('guardados_toggle', { reporte_id: id });
                    button.classList.toggle('is-active', result.action === 'saved');
                    button.querySelector('i').textContent = result.action === 'saved' ? 'Guardado' : 'Guardar';
                } else if (action === 'share') {
                    const url = detailBase + id;
                    if (navigator.share) await navigator.share({ title: 'ReporteCiudadano', url: url });
                    else if (navigator.clipboard) await navigator.clipboard.writeText(url);
                    const result = await postAction('compartidos_crear', { reporte_id: id });
                    card.querySelector('[data-count="shares"]').textContent = result.total;
                }
            } catch (error) {
                window.alert(error.message);
            } finally {
                button.disabled = false;
            }
        });

        const commentForm = card.querySelector('.rc-quick-comment');
        commentForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            const submit = commentForm.querySelector('button');
            const message = commentForm.querySelector('.rc-inline-message');
            submit.disabled = true;
            try {
                const result = await postAction('comentarios_crear', new FormData(commentForm));
                commentForm.reset();
                const counter = card.querySelector('[data-count="comments"]');
                counter.textContent = Number(counter.textContent || 0) + 1;
                message.textContent = result.message;
                message.className = 'rc-inline-message is-success';
            } catch (error) {
                message.textContent = error.message;
                message.className = 'rc-inline-message is-error';
            } finally {
                submit.disabled = false;
            }
        });
    });
});
</script>
