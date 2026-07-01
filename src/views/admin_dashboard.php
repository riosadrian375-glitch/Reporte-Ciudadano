<?php
if (!esRol('admin_municipal')) {
    http_response_code(403);
    echo '<section class="rc-dashboard"><div class="rc-empty">Acceso exclusivo para el administrador municipal.</div></section>';
    return;
}

$estadosPermitidos = ['pendiente', 'en_proceso', 'resuelto', 'rechazado'];
$estadoFiltro = $_GET['estado'] ?? 'pendiente';
$categoriaFiltro = (int)($_GET['categoria_id'] ?? 0);
$distritoFiltro = (int)($_GET['distrito_id'] ?? 0);
$filtrosAdmin = [];
if (in_array($estadoFiltro, $estadosPermitidos, true)) $filtrosAdmin['estado'] = $estadoFiltro;
if ($categoriaFiltro) $filtrosAdmin['categoria_id'] = $categoriaFiltro;
if ($distritoFiltro) $filtrosAdmin['distrito_id'] = $distritoFiltro;

$reportesAdmin = Reporte::listarTodosAdmin($filtrosAdmin);
$todosReportesAdmin = Reporte::listarTodosAdmin();
$operadoresAdmin = Usuario::listarOperadoresConCarga();
$categoriasAdmin = Categoria::listar();
$distritosAdmin = Distrito::listar();
$conteoEstados = ['pendiente' => 0, 'en_proceso' => 0, 'resuelto' => 0, 'rechazado' => 0];
foreach (Reporte::contarPorEstado() as $filaEstado) {
    $conteoEstados[$filaEstado['estado']] = (int)$filaEstado['total'];
}

function rcAdminClima($valor) {
    if (!$valor) return 'Sin registro climático';
    $clima = is_array($valor) ? $valor : json_decode($valor, true);
    if (!is_array($clima)) return 'Sin registro climático';
    $descripcion = ucfirst((string)($clima['descripcion'] ?? 'Sin descripción'));
    $temperatura = isset($clima['temperatura']) ? ' · ' . $clima['temperatura'] . ' °C' : '';
    return $descripcion . $temperatura;
}

$reportesAdminJs = [];
foreach ($reportesAdmin as $reporteAdmin) {
    $reportesAdminJs[(string)$reporteAdmin['id']] = [
        'id' => (int)$reporteAdmin['id'],
        'titulo' => $reporteAdmin['titulo'],
        'descripcion' => $reporteAdmin['descripcion'],
        'categoria' => $reporteAdmin['categoria_nombre'],
        'distrito' => $reporteAdmin['distrito_nombre'],
        'fecha' => date('d/m/Y H:i', strtotime($reporteAdmin['fecha_creacion'])),
        'clima' => rcAdminClima($reporteAdmin['clima_momento'] ?? null),
        'estado' => $reporteAdmin['estado'],
    ];
}
$primerReporteAdmin = $reportesAdmin ? reset($reportesAdmin) : null;
?>

<section class="rc-dashboard rc-admin-dashboard">
    <div class="rc-shell">
        <header class="rc-page-heading">
            <div>
                <span class="rc-eyebrow">MUNICIPALIDAD · AREQUIPA</span>
                <h1>Consola de gestión</h1>
                <p>Revisión, priorización y asignación de incidencias urbanas.</p>
            </div>
            <span class="rc-live-indicator"><i></i> Sesión operativa</span>
        </header>

        <div class="rc-stats" aria-label="Resumen estadístico">
            <article class="rc-stat"><span>Total recibidos</span><strong><?= count($todosReportesAdmin) ?></strong></article>
            <article class="rc-stat rc-stat-pending"><span>Pendientes</span><strong><?= $conteoEstados['pendiente'] ?></strong></article>
            <article class="rc-stat rc-stat-progress"><span>En proceso</span><strong><?= $conteoEstados['en_proceso'] ?></strong></article>
            <article class="rc-stat rc-stat-success"><span>Resueltos</span><strong><?= $conteoEstados['resuelto'] ?></strong></article>
        </div>

        <div class="rc-admin-grid">
            <section class="rc-panel rc-management-console" aria-labelledby="reportes-title">
                <div class="rc-panel-heading">
                    <div>
                        <span class="rc-label">BANDEJA DE ENTRADA</span>
                        <h2 id="reportes-title">Reportes recibidos</h2>
                    </div>
                    <span class="rc-counter"><?= count($reportesAdmin) ?> visibles</span>
                </div>

                <form class="rc-filter-form" method="get" action="<?= SITE_URL ?>/index.php">
                    <input type="hidden" name="view" value="admin_dashboard">
                    <label>
                        <span>ESTADO</span>
                        <select name="estado">
                            <option value="todos" <?= $estadoFiltro === 'todos' ? 'selected' : '' ?>>Todos</option>
                            <option value="pendiente" <?= $estadoFiltro === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="en_proceso" <?= $estadoFiltro === 'en_proceso' ? 'selected' : '' ?>>En proceso</option>
                            <option value="resuelto" <?= $estadoFiltro === 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
                            <option value="rechazado" <?= $estadoFiltro === 'rechazado' ? 'selected' : '' ?>>Rechazado</option>
                        </select>
                    </label>
                    <label>
                        <span>CATEGORÍA</span>
                        <select name="categoria_id">
                            <option value="0">Todas</option>
                            <?php foreach ($categoriasAdmin as $categoriaAdmin): ?>
                                <option value="<?= (int)$categoriaAdmin['id'] ?>" <?= $categoriaFiltro === (int)$categoriaAdmin['id'] ? 'selected' : '' ?>><?= htmlspecialchars($categoriaAdmin['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span>DISTRITO</span>
                        <select name="distrito_id">
                            <option value="0">Todos</option>
                            <?php foreach ($distritosAdmin as $distritoAdmin): ?>
                                <option value="<?= (int)$distritoAdmin['id'] ?>" <?= $distritoFiltro === (int)$distritoAdmin['id'] ? 'selected' : '' ?>><?= htmlspecialchars($distritoAdmin['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button type="submit" class="rc-btn rc-btn-secondary">Aplicar filtros</button>
                </form>

                <?php if ($reportesAdmin): ?>
                    <div class="rc-table-wrap">
                        <table class="rc-table">
                            <thead>
                                <tr><th>Incidencia</th><th>Distrito</th><th>Fecha</th><th>Clima</th><th>Estado</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportesAdmin as $indiceAdmin => $reporteAdmin): ?>
                                    <tr class="rc-report-row <?= $indiceAdmin === 0 ? 'is-selected' : '' ?>" tabindex="0" data-report-id="<?= (int)$reporteAdmin['id'] ?>">
                                        <td>
                                            <span class="rc-category"><?= htmlspecialchars(mb_strtoupper($reporteAdmin['categoria_nombre'])) ?></span>
                                            <strong><?= htmlspecialchars($reporteAdmin['titulo']) ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($reporteAdmin['distrito_nombre']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($reporteAdmin['fecha_creacion'])) ?></td>
                                        <td><span class="rc-weather-tag"><?= htmlspecialchars(rcAdminClima($reporteAdmin['clima_momento'] ?? null)) ?></span></td>
                                        <td><span class="rc-status rc-status-<?= htmlspecialchars($reporteAdmin['estado']) ?>"><?= htmlspecialchars(str_replace('_', ' ', mb_strtoupper($reporteAdmin['estado']))) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="rc-empty">
                        <strong>No hay reportes con estos filtros.</strong>
                        <span>Prueba otra combinación o vuelve a la bandeja de pendientes.</span>
                    </div>
                <?php endif; ?>
            </section>

            <aside class="rc-panel rc-assignment-panel" aria-labelledby="assignment-title">
                <div class="rc-panel-heading">
                    <div>
                        <span class="rc-label">MÓDULO DE ASIGNACIÓN</span>
                        <h2 id="assignment-title">Delegar atención</h2>
                    </div>
                </div>

                <div id="rcAdminSelection" class="rc-selection <?= $primerReporteAdmin ? '' : 'is-empty' ?>">
                    <span class="rc-category" id="rcSelectedCategory"><?= $primerReporteAdmin ? htmlspecialchars(mb_strtoupper($primerReporteAdmin['categoria_nombre'])) : 'SIN SELECCIÓN' ?></span>
                    <h3 id="rcSelectedTitle"><?= $primerReporteAdmin ? htmlspecialchars($primerReporteAdmin['titulo']) : 'Selecciona un reporte' ?></h3>
                    <p id="rcSelectedDescription"><?= $primerReporteAdmin ? htmlspecialchars(resumirTexto($primerReporteAdmin['descripcion'], 210)) : 'El detalle del reporte aparecerá aquí.' ?></p>
                    <dl class="rc-detail-list">
                        <div><dt>DISTRITO</dt><dd id="rcSelectedDistrict"><?= $primerReporteAdmin ? htmlspecialchars($primerReporteAdmin['distrito_nombre']) : '—' ?></dd></div>
                        <div><dt>CLIMA CAPTURADO</dt><dd id="rcSelectedWeather"><?= $primerReporteAdmin ? htmlspecialchars(rcAdminClima($primerReporteAdmin['clima_momento'] ?? null)) : '—' ?></dd></div>
                        <div><dt>REGISTRO</dt><dd id="rcSelectedDate"><?= $primerReporteAdmin ? date('d/m/Y H:i', strtotime($primerReporteAdmin['fecha_creacion'])) : '—' ?></dd></div>
                    </dl>
                </div>

                <form id="rcAssignmentForm" class="rc-stack-form" method="post">
                    <input type="hidden" name="reporte_id" id="rcSelectedReportId" value="<?= $primerReporteAdmin ? (int)$primerReporteAdmin['id'] : '' ?>">
                    <label>
                        <span>OPERADOR TÉCNICO · CARGA ACTUAL</span>
                        <select name="operador_id" required <?= !$primerReporteAdmin ? 'disabled' : '' ?>>
                            <option value="">Seleccionar operador disponible</option>
                            <?php foreach ($operadoresAdmin as $operadorAdmin): ?>
                                <option value="<?= (int)$operadorAdmin['id'] ?>"><?= htmlspecialchars($operadorAdmin['nombre']) ?> · <?= (int)$operadorAdmin['carga_actual'] ?> asignados</option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="rc-form-message" id="rcAssignmentMessage" role="status"></div>
                    <button type="submit" class="rc-btn rc-btn-primary" <?= (!$primerReporteAdmin || !$operadoresAdmin || ($primerReporteAdmin['estado'] ?? '') !== 'pendiente') ? 'disabled' : '' ?>>Asignar reporte</button>
                    <?php if (!$operadoresAdmin): ?><small>No hay operadores activos disponibles.</small><?php endif; ?>
                </form>
            </aside>
        </div>
    </div>

    <details class="rc-emergency-fab">
        <summary>EMERGENCIAS</summary>
        <nav aria-label="Contactos de emergencia de Arequipa">
            <a href="tel:105"><span>POLICÍA</span><strong>105</strong></a>
            <a href="tel:116"><span>BOMBEROS</span><strong>116</strong></a>
            <a href="tel:106"><span>SAMU</span><strong>106</strong></a>
        </nav>
    </details>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const reports = <?= json_encode($reportesAdminJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const rows = document.querySelectorAll('.rc-report-row');
    const form = document.getElementById('rcAssignmentForm');
    const message = document.getElementById('rcAssignmentMessage');

    function selectReport(id) {
        const report = reports[String(id)];
        if (!report) return;
        rows.forEach(function (row) { row.classList.toggle('is-selected', row.dataset.reportId === String(id)); });
        document.getElementById('rcSelectedReportId').value = report.id;
        document.getElementById('rcSelectedCategory').textContent = report.categoria.toUpperCase();
        document.getElementById('rcSelectedTitle').textContent = report.titulo;
        document.getElementById('rcSelectedDescription').textContent = report.descripcion;
        document.getElementById('rcSelectedDistrict').textContent = report.distrito;
        document.getElementById('rcSelectedWeather').textContent = report.clima;
        document.getElementById('rcSelectedDate').textContent = report.fecha;
        form.querySelector('select').disabled = report.estado !== 'pendiente';
        form.querySelector('button[type="submit"]').disabled = report.estado !== 'pendiente' || form.querySelector('select').options.length <= 1;
        message.textContent = report.estado === 'pendiente' ? '' : 'Solo los reportes pendientes pueden asignarse.';
        message.className = 'rc-form-message';
    }

    rows.forEach(function (row) {
        row.addEventListener('click', function () { selectReport(this.dataset.reportId); });
        row.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); selectReport(this.dataset.reportId); }
        });
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = 'Asignando…';
        message.textContent = '';
        try {
            const response = await fetch('<?= SITE_URL ?>/index.php?view=api&action=asignar_reporte', { method: 'POST', body: new FormData(form) });
            const data = await response.json();
            if (!data.success) throw new Error(data.error || 'No se pudo asignar el reporte.');
            message.textContent = data.message;
            message.className = 'rc-form-message is-success';
            window.setTimeout(function () { window.location.reload(); }, 700);
        } catch (error) {
            message.textContent = error.message;
            message.className = 'rc-form-message is-error';
            button.disabled = false;
            button.textContent = 'Asignar reporte';
        }
    });
});
</script>
