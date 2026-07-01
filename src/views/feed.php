<?php
$categoriaActiva = isset($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : 0;
$distritoActivo = isset($_GET['distrito_id']) ? (int)$_GET['distrito_id'] : 0;
$estadoActivo = $_GET['estado'] ?? '';
$ordenActivo = $_GET['orden'] ?? 'recientes';
?>

<section class="feed-header">
    <div class="container">
        <div class="feed-welcome">
            <div>
                <h1 class="feed-title">Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Ciudadano') ?></h1>
                <p class="feed-subtitle">Explora los reportes de tu comunidad en Arequipa</p>
            </div>
            <a href="<?= SITE_URL ?>/index.php?view=reporte/crear" class="btn btn-primary feed-create-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Nuevo Reporte
            </a>
        </div>

        <?php require_once __DIR__ . '/partials/clima-widget.php'; ?>

        <div class="filter-bar" id="filterBar">
            <div class="filter-chips" id="filterChips">
                <button class="chip <?= $categoriaActiva === 0 ? 'chip-active' : '' ?>" data-filter="categoria_id" data-value="">Todas</button>
                <?php $categorias = Categoria::listar(); foreach ($categorias as $c): ?>
                <?php
                    $iconoCategoria = trim((string)($c['icono'] ?? ''));
                    $mostrarIconoCategoria = $iconoCategoria !== '' && !preg_match('/^[a-zA-Z0-9_ -]+$/u', $iconoCategoria);
                ?>
                <button class="chip <?= $categoriaActiva === (int)$c['id'] ? 'chip-active' : '' ?>" data-filter="categoria_id" data-value="<?= $c['id'] ?>">
                    <?php if ($mostrarIconoCategoria): ?><span class="chip-icon"><?= htmlspecialchars($iconoCategoria) ?></span><?php endif; ?>
                    <?= htmlspecialchars($c['nombre']) ?>
                </button>
                <?php endforeach; ?>
            </div>
            <div class="filter-chips">
                <button class="chip <?= $distritoActivo === 0 ? 'chip-active' : '' ?>" data-filter="distrito_id" data-value="">Todos los distritos</button>
                <?php $distritos = Distrito::listar(); foreach ($distritos as $d): ?>
                <button class="chip <?= $distritoActivo === (int)$d['id'] ? 'chip-active' : '' ?>" data-filter="distrito_id" data-value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nombre']) ?></button>
                <?php endforeach; ?>
            </div>
            <div class="filter-row">
                <div class="filter-chips">
                    <button class="chip <?= $estadoActivo === '' ? 'chip-active' : '' ?>" data-filter="estado" data-value="">Todos los estados</button>
                    <button class="chip <?= $estadoActivo === 'pendiente' ? 'chip-active' : '' ?>" data-filter="estado" data-value="pendiente">Pendiente</button>
                    <button class="chip <?= $estadoActivo === 'en_proceso' ? 'chip-active' : '' ?>" data-filter="estado" data-value="en_proceso">En Proceso</button>
                    <button class="chip <?= $estadoActivo === 'resuelto' ? 'chip-active' : '' ?>" data-filter="estado" data-value="resuelto">Resuelto</button>
                    <button class="chip <?= $estadoActivo === 'rechazado' ? 'chip-active' : '' ?>" data-filter="estado" data-value="rechazado">Rechazado</button>
                </div>
                <div class="filter-sort">
                    <label for="sortOrder">Ordenar:</label>
                    <select id="sortOrder" class="form-input form-input-sm" data-filter="orden">
                        <option value="recientes" <?= $ordenActivo === 'recientes' ? 'selected' : '' ?>>Mas recientes</option>
                        <option value="antiguos" <?= $ordenActivo === 'antiguos' ? 'selected' : '' ?>>Mas antiguos</option>
                        <option value="populares" <?= $ordenActivo === 'populares' ? 'selected' : '' ?>>Mas populares</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="feed-content">
    <div class="container">
        <div class="card-grid" id="reporteGrid">
            <?php
            $filtros = [];
            if (!empty($_GET['categoria_id'])) $filtros['categoria_id'] = (int)$_GET['categoria_id'];
            if (!empty($_GET['distrito_id'])) $filtros['distrito_id'] = (int)$_GET['distrito_id'];
            if (!empty($_GET['estado'])) $filtros['estado'] = $_GET['estado'];
            if (!empty($_GET['buscar'])) $filtros['buscar'] = $_GET['buscar'];
            if (!empty($_GET['orden'])) $filtros['orden'] = $_GET['orden'];
            $reportes = Reporte::listar($filtros);
            ?>
            <?php if (!empty($reportes)): ?>
                <?php foreach ($reportes as $reporte): ?>
                    <?php require __DIR__ . '/partials/tarjeta-reporte.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="feed-empty">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#9E9E9E" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    <h3>No se encontraron reportes</h3>
                    <p>Se el primero en reportar algo en tu comunidad.</p>
                    <a href="<?= SITE_URL ?>/index.php?view=reporte/crear" class="btn btn-primary">Crear Reporte</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var siteUrl = '<?= SITE_URL ?>';
    var activeFilters = {
        categoria_id: '<?= $categoriaActiva ?: '' ?>',
        distrito_id: '<?= $distritoActivo ?: '' ?>',
        estado: '<?= htmlspecialchars($estadoActivo, ENT_QUOTES) ?>',
        orden: '<?= htmlspecialchars($ordenActivo, ENT_QUOTES) ?>'
    };
    var filterChips = document.querySelectorAll('.filter-bar .chip');
    filterChips.forEach(function(chip) {
        chip.addEventListener('click', function() {
            var filter = this.dataset.filter;
            var value = this.dataset.value;
            var siblings = this.parentElement.querySelectorAll('.chip');
            siblings.forEach(function(s) { s.classList.remove('chip-active'); });
            this.classList.add('chip-active');
            activeFilters[filter] = value;
            cargarReportes();
        });
    });
    document.getElementById('sortOrder').addEventListener('change', function() {
        activeFilters.orden = this.value;
        cargarReportes();
    });

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function(match) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[match];
        });
    }

    function resumir(texto, limite) {
        texto = String(texto || '');
        limite = limite || 120;
        return texto.length > limite ? texto.slice(0, limite).trim() + '...' : texto;
    }

    function tiempoRelativo(fechaStr) {
        if (!fechaStr) return '';
        var fecha = new Date(String(fechaStr).replace(' ', 'T'));
        if (isNaN(fecha.getTime())) return '';
        var diffMs = Date.now() - fecha.getTime();
        var diffMin = Math.floor(diffMs / 60000);
        var diffHoras = Math.floor(diffMs / 3600000);
        var diffDias = Math.floor(diffMs / 86400000);
        if (diffMin < 1) return 'ahora';
        if (diffMin < 60) return 'hace ' + diffMin + ' min';
        if (diffHoras < 24) return 'hace ' + diffHoras + ' h';
        if (diffDias < 7) return 'hace ' + diffDias + ' d';
        return fecha.toLocaleDateString('es-PE', { day: 'numeric', month: 'short' });
    }

    function estadoTexto(estado) {
        return String(estado || 'pendiente').replace('_', ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
    }

    function esIconoVisual(icono) {
        icono = String(icono || '').trim();
        return icono !== '' && !/^[a-zA-Z0-9_ -]+$/.test(icono);
    }

    function tarjetaReporte(r) {
        var detalleUrl = siteUrl + '/index.php?view=reporte/detalle&id=' + encodeURIComponent(r.id);
        var img = r.imagen_thumbnail
            ? '<img src="' + siteUrl + '/' + escapeHtml(r.imagen_thumbnail) + '" alt="' + escapeHtml(r.titulo) + '" loading="lazy">'
            : '<div class="card-image-placeholder"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/><path d="M21 15l-5-5L5 21"/></svg></div>';
        var icono = esIconoVisual(r.categoria_icono) ? '<span class="chip-icon">' + escapeHtml(r.categoria_icono) + '</span>' : '';
        var urgente = r.es_urgente ? '<span class="chip chip-urgente">Urgente</span>' : '';

        return '<div class="card reporte-card" data-reporte-id="' + escapeHtml(r.id) + '">' +
            '<div class="card-image"><a href="' + detalleUrl + '">' + img + '</a>' +
            '<div class="card-badges"><span class="chip chip-categoria">' + icono + escapeHtml(r.categoria_nombre || '') + '</span>' + urgente + '</div></div>' +
            '<div class="card-body"><div class="card-meta"><span class="card-distrito">' + escapeHtml(r.distrito_nombre || '') + '</span><span class="card-date">' + escapeHtml(tiempoRelativo(r.fecha_creacion)) + '</span></div>' +
            '<h3 class="card-title"><a href="' + detalleUrl + '">' + escapeHtml(r.titulo || '') + '</a></h3>' +
            '<p class="card-desc">' + escapeHtml(resumir(r.descripcion, 120)) + '</p>' +
            '<div class="card-footer"><div class="card-actions">' +
            '<button class="btn-action btn-like" data-action="like" data-reporte-id="' + escapeHtml(r.id) + '"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg><span class="count like-count">' + parseInt(r.total_likes || 0, 10) + '</span></button>' +
            '<button class="btn-action btn-save" data-action="save" data-reporte-id="' + escapeHtml(r.id) + '"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg></button>' +
            '<button class="btn-action btn-share" data-action="share" data-reporte-id="' + escapeHtml(r.id) + '"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/></svg><span class="count">' + parseInt(r.total_compartidos || 0, 10) + '</span></button>' +
            '<a href="' + detalleUrl + '#comentarios" class="btn-action"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg><span class="count">' + parseInt(r.total_comentarios || 0, 10) + '</span></a>' +
            '</div><div class="card-status"><span class="status-badge status-' + escapeHtml(r.estado || 'pendiente') + '">' + escapeHtml(estadoTexto(r.estado)) + '</span></div></div>' +
            '<button class="btn-ia-card" data-action="ia-ask" data-reporte-id="' + escapeHtml(r.id) + '"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a4 4 0 014 4c0 2-2 4-4 4s-4-2-4-4 2-4 4-4z"/><path d="M2 22v-2a6 6 0 016-6h8a6 6 0 016 6v2"/></svg>Preguntar a la IA</button></div></div>';
    }

    function cargarReportes() {
        var params = new URLSearchParams();
        params.set('view', 'api');
        params.set('action', 'reportes_listar');
        Object.keys(activeFilters).forEach(function(k) {
            if (activeFilters[k]) params.set(k, activeFilters[k]);
        });
        var buscar = new URLSearchParams(window.location.search).get('buscar');
        if (buscar) params.set('buscar', buscar);

        var grid = document.getElementById('reporteGrid');
        if (grid) grid.classList.add('is-loading');
        fetch(siteUrl + '/index.php?' + params.toString())
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success && data.reportes) {
                    if (data.reportes.length === 0) {
                        grid.innerHTML = '<div class="feed-empty"><svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#9E9E9E" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg><h3>No se encontraron reportes</h3><p>Intenta con otros filtros.</p></div>';
                        return;
                    }
                    grid.innerHTML = data.reportes.map(tarjetaReporte).join('');
                }
            })
            .catch(function() {
                grid.innerHTML = '<div class="feed-empty"><h3>No se pudo cargar el feed</h3><p>Revisa la conexion del servidor e intenta nuevamente.</p></div>';
            })
            .finally(function() {
                if (grid) grid.classList.remove('is-loading');
            });
    }
});
</script>
