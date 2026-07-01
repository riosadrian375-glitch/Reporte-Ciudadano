<?php
if (!esRol('admin_sistema')) {
    http_response_code(403);
    echo '<section class="rc-dashboard"><div class="rc-empty">Acceso exclusivo para el administrador del sistema.</div></section>';
    return;
}

$usuariosSistema = Usuario::listarTodos();
$logsSistema = ActividadLog::listar(120);
$backupsSistema = (new MantenimientoService())->listarBackups();

function rcSistemaBytes($bytes) {
    $bytes = (int)$bytes;
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

$activosSistema = count(array_filter($usuariosSistema, function ($u) { return $u['estado'] === 'activo'; }));
$inactivosSistema = count($usuariosSistema) - $activosSistema;
?>

<section class="rc-dashboard rc-system-dashboard">
    <div class="rc-shell">
        <header class="rc-page-heading">
            <div>
                <span class="rc-eyebrow">ADMINISTRACIÓN DEL SISTEMA</span>
                <h1>Consola técnica</h1>
                <p>Usuarios, trazabilidad de servicios y continuidad operativa.</p>
            </div>
            <div class="rc-system-health"><span>PLATAFORMA</span><strong><i></i> OPERATIVA</strong></div>
        </header>

        <div class="rc-stats rc-system-stats">
            <article class="rc-stat"><span>Usuarios registrados</span><strong><?= count($usuariosSistema) ?></strong></article>
            <article class="rc-stat rc-stat-success"><span>Cuentas activas</span><strong><?= $activosSistema ?></strong></article>
            <article class="rc-stat rc-stat-pending"><span>Cuentas inactivas</span><strong><?= $inactivosSistema ?></strong></article>
            <article class="rc-stat"><span>Backups disponibles</span><strong id="rcBackupCount"><?= count($backupsSistema) ?></strong></article>
        </div>

        <nav class="rc-tabs" aria-label="Módulos de administración">
            <button type="button" class="is-active" data-tab="users">GESTIÓN DE USUARIOS</button>
            <button type="button" data-tab="logs">LOGS DE ACTIVIDAD</button>
            <button type="button" data-tab="backups">BACKUPS</button>
        </nav>

        <section class="rc-tab-panel is-active" data-panel="users">
            <div class="rc-panel-heading">
                <div><span class="rc-label">CONTROL DE ACCESO</span><h2>Usuarios y roles</h2></div>
                <span class="rc-counter"><?= count($usuariosSistema) ?> cuentas</span>
            </div>
            <div class="rc-filter-form rc-user-filters">
                <label><span>BUSCAR</span><input type="search" id="rcUserSearch" placeholder="Nombre o correo"></label>
                <label><span>ROL</span><select id="rcUserRoleFilter"><option value="">Todos</option><option value="ciudadano">Ciudadano</option><option value="operador">Operador</option><option value="admin_municipal">Admin municipal</option><option value="admin_sistema">Admin sistema</option></select></label>
                <label><span>ESTADO</span><select id="rcUserStatusFilter"><option value="">Todos</option><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select></label>
            </div>
            <div class="rc-table-wrap">
                <table class="rc-table rc-users-table">
                    <thead><tr><th>Usuario</th><th>Distrito</th><th>Rol</th><th>Estado</th><th>Último acceso</th><th>Acción</th></tr></thead>
                    <tbody>
                        <?php foreach ($usuariosSistema as $usuarioSistema):
                            $estadoNormalizado = $usuarioSistema['estado'] === 'activo' ? 'activo' : 'inactivo';
                        ?>
                            <tr data-user-id="<?= (int)$usuarioSistema['id'] ?>" data-search="<?= htmlspecialchars(mb_strtolower($usuarioSistema['nombre'] . ' ' . $usuarioSistema['correo'])) ?>" data-role="<?= htmlspecialchars($usuarioSistema['rol']) ?>" data-status="<?= $estadoNormalizado ?>">
                                <td><strong><?= htmlspecialchars($usuarioSistema['nombre']) ?></strong><span><?= htmlspecialchars($usuarioSistema['correo']) ?></span></td>
                                <td><?= htmlspecialchars($usuarioSistema['distrito_nombre'] ?? '—') ?></td>
                                <td>
                                    <select class="rc-role-select" aria-label="Rol de <?= htmlspecialchars($usuarioSistema['nombre']) ?>">
                                        <option value="ciudadano" <?= $usuarioSistema['rol'] === 'ciudadano' ? 'selected' : '' ?>>Ciudadano</option>
                                        <option value="operador" <?= $usuarioSistema['rol'] === 'operador' ? 'selected' : '' ?>>Operador</option>
                                        <option value="admin_municipal" <?= $usuarioSistema['rol'] === 'admin_municipal' ? 'selected' : '' ?>>Admin municipal</option>
                                        <option value="admin_sistema" <?= $usuarioSistema['rol'] === 'admin_sistema' ? 'selected' : '' ?>>Admin sistema</option>
                                    </select>
                                </td>
                                <td><span class="rc-status rc-status-<?= $estadoNormalizado === 'activo' ? 'resuelto' : 'rechazado' ?>"><?= mb_strtoupper($estadoNormalizado) ?></span></td>
                                <td><?= $usuarioSistema['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuarioSistema['ultimo_acceso'])) : 'Sin registro' ?></td>
                                <td>
                                    <?php if ((int)$usuarioSistema['id'] === (int)$_SESSION['usuario_id']): ?>
                                        <span class="rc-self-label">TU CUENTA</span>
                                    <?php elseif ($estadoNormalizado === 'activo'): ?>
                                        <button type="button" class="rc-btn rc-btn-danger rc-user-state" data-action="suspender">Suspender</button>
                                    <?php else: ?>
                                        <button type="button" class="rc-btn rc-btn-secondary rc-user-state" data-action="activar">Activar</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="rc-form-message" id="rcUserMessage" role="status"></div>
        </section>

        <section class="rc-tab-panel" data-panel="logs">
            <div class="rc-panel-heading">
                <div><span class="rc-label">TRAZABILIDAD</span><h2>Actividad de IA y APIs</h2></div>
                <button type="button" class="rc-btn rc-btn-secondary" id="rcReloadLogs">Ver logs del servidor</button>
            </div>
            <div class="rc-table-wrap">
                <table class="rc-table">
                    <thead><tr><th>Fecha</th><th>Módulo</th><th>Acción</th><th>Usuario</th><th>Resultado</th><th>Detalle</th></tr></thead>
                    <tbody id="rcLogsBody">
                        <?php foreach ($logsSistema as $logSistema): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i:s', strtotime($logSistema['fecha'])) ?></td>
                                <td><span class="rc-category"><?= htmlspecialchars(mb_strtoupper($logSistema['modulo'])) ?></span></td>
                                <td><?= htmlspecialchars($logSistema['accion']) ?></td>
                                <td><?= htmlspecialchars($logSistema['usuario_nombre'] ?? 'Sistema / anónimo') ?></td>
                                <td><span class="rc-status rc-status-<?= $logSistema['estado'] === 'ok' ? 'resuelto' : 'rechazado' ?>"><?= htmlspecialchars(mb_strtoupper($logSistema['estado'])) ?></span></td>
                                <td><?= htmlspecialchars($logSistema['detalle'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$logsSistema): ?><tr><td colspan="6">No hay logs disponibles. Ejecuta la migración de paneles para habilitarlos.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rc-tab-panel" data-panel="backups">
            <div class="rc-backup-layout">
                <div class="rc-panel rc-backup-action">
                    <span class="rc-label">MANTENIMIENTO MYSQL</span>
                    <h2>Copia de seguridad</h2>
                    <p>Genera un archivo SQL consistente con estructura y datos de todas las tablas. Se almacena fuera del directorio público.</p>
                    <button type="button" class="rc-btn rc-btn-secondary" id="rcGenerateBackup">Generar copia de seguridad MySQL</button>
                    <div class="rc-form-message" id="rcBackupMessage" role="status"></div>
                </div>
                <div class="rc-panel">
                    <div class="rc-panel-heading"><div><span class="rc-label">HISTORIAL</span><h2>Backups generados</h2></div></div>
                    <div class="rc-backup-list" id="rcBackupList">
                        <?php foreach ($backupsSistema as $backupSistema): ?>
                            <article><div><strong><?= htmlspecialchars($backupSistema['nombre']) ?></strong><span><?= htmlspecialchars($backupSistema['fecha']) ?></span></div><b><?= htmlspecialchars(rcSistemaBytes($backupSistema['tamano'])) ?></b></article>
                        <?php endforeach; ?>
                        <?php if (!$backupsSistema): ?><div class="rc-empty"><strong>No hay backups todavía.</strong><span>Genera la primera copia desde este panel.</span></div><?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const apiUrl = '<?= SITE_URL ?>/index.php?view=api&action=';
    const userMessage = document.getElementById('rcUserMessage');

    async function post(action, data) {
        const response = await fetch(apiUrl + action, { method: 'POST', body: data instanceof FormData ? data : new URLSearchParams(data) });
        const result = await response.json();
        if (!result.success) throw new Error(result.error || 'La operación no pudo completarse.');
        return result;
    }

    document.querySelectorAll('.rc-tabs button').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.rc-tabs button').forEach(function (item) { item.classList.remove('is-active'); });
            document.querySelectorAll('.rc-tab-panel').forEach(function (panel) { panel.classList.remove('is-active'); });
            tab.classList.add('is-active');
            document.querySelector('[data-panel="' + tab.dataset.tab + '"]').classList.add('is-active');
        });
    });

    function filterUsers() {
        const search = document.getElementById('rcUserSearch').value.trim().toLowerCase();
        const role = document.getElementById('rcUserRoleFilter').value;
        const status = document.getElementById('rcUserStatusFilter').value;
        document.querySelectorAll('.rc-users-table tbody tr').forEach(function (row) {
            row.hidden = Boolean((search && !row.dataset.search.includes(search)) || (role && row.dataset.role !== role) || (status && row.dataset.status !== status));
        });
    }
    ['rcUserSearch', 'rcUserRoleFilter', 'rcUserStatusFilter'].forEach(function (id) {
        document.getElementById(id).addEventListener(id === 'rcUserSearch' ? 'input' : 'change', filterUsers);
    });

    document.querySelectorAll('.rc-role-select').forEach(function (select) {
        let initial = select.value;
        select.addEventListener('change', async function () {
            const row = select.closest('tr');
            select.disabled = true;
            try {
                const result = await post('sistema_usuario_actualizar', { id: row.dataset.userId, rol: select.value });
                row.dataset.role = select.value;
                initial = select.value;
                userMessage.textContent = result.message;
                userMessage.className = 'rc-form-message is-success';
            } catch (error) {
                select.value = initial;
                userMessage.textContent = error.message;
                userMessage.className = 'rc-form-message is-error';
            } finally { select.disabled = false; }
        });
    });

    document.querySelectorAll('.rc-user-state').forEach(function (button) {
        button.addEventListener('click', async function () {
            const row = button.closest('tr');
            if (!window.confirm('¿Confirmas esta actualización de cuenta?')) return;
            button.disabled = true;
            try {
                await post('sistema_usuario_estado', { id: row.dataset.userId, accion: button.dataset.action });
                window.location.reload();
            } catch (error) {
                userMessage.textContent = error.message;
                userMessage.className = 'rc-form-message is-error';
                button.disabled = false;
            }
        });
    });

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>'"]/g, function (char) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[char];
        });
    }

    document.getElementById('rcReloadLogs').addEventListener('click', async function () {
        const button = this;
        button.disabled = true;
        try {
            const response = await fetch(apiUrl + 'sistema_logs');
            const result = await response.json();
            if (!result.success) throw new Error(result.error || 'No se pudieron cargar los logs.');
            document.getElementById('rcLogsBody').innerHTML = result.logs.length ? result.logs.map(function (log) {
                const status = log.estado === 'ok' ? 'resuelto' : 'rechazado';
                return '<tr><td>' + escapeHtml(log.fecha) + '</td><td><span class="rc-category">' + escapeHtml(log.modulo.toUpperCase()) + '</span></td><td>' + escapeHtml(log.accion) + '</td><td>' + escapeHtml(log.usuario_nombre || 'Sistema / anónimo') + '</td><td><span class="rc-status rc-status-' + status + '">' + escapeHtml(log.estado.toUpperCase()) + '</span></td><td>' + escapeHtml(log.detalle || '—') + '</td></tr>';
            }).join('') : '<tr><td colspan="6">No hay logs disponibles.</td></tr>';
        } catch (error) {
            window.alert(error.message);
        } finally { button.disabled = false; }
    });

    document.getElementById('rcGenerateBackup').addEventListener('click', async function () {
        const button = this;
        const message = document.getElementById('rcBackupMessage');
        button.disabled = true;
        button.textContent = 'Generando…';
        try {
            const result = await post('sistema_backup_generar', {});
            message.textContent = result.message + ' ' + result.backup.nombre;
            message.className = 'rc-form-message is-success';
            window.setTimeout(function () { window.location.reload(); }, 900);
        } catch (error) {
            message.textContent = error.message;
            message.className = 'rc-form-message is-error';
            button.disabled = false;
            button.textContent = 'Generar copia de seguridad MySQL';
        }
    });
});
</script>
