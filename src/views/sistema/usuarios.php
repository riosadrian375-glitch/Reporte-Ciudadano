<section class="page-header">
    <div class="container">
        <h1 class="page-title">Gestion de Usuarios</h1>
        <p class="page-subtitle">Administra todos los usuarios de la plataforma</p>
    </div>
</section>
<section class="page-content">
    <div class="container">
        <?php $usuarios = Usuario::listarTodos(); ?>
        <div class="card">
            <div class="card-header">
                <h3>Usuarios <span class="badge"><?= count($usuarios) ?></span></h3>
                <div class="admin-filters">
                    <input type="text" id="buscarUsuario" class="form-input form-input-sm" placeholder="Buscar por nombre o correo...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table" id="usuariosTable">
                    <thead>
                        <tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Distrito</th><th>Estado</th><th>Ultimo acceso</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= htmlspecialchars($u['nombre']) ?></td>
                            <td><?= htmlspecialchars($u['correo']) ?></td>
                            <td>
                                <select class="form-input form-input-sm cambio-rol" data-id="<?= $u['id'] ?>">
                                    <option value="ciudadano" <?= $u['rol'] === 'ciudadano' ? 'selected' : '' ?>>Ciudadano</option>
                                    <option value="operador" <?= $u['rol'] === 'operador' ? 'selected' : '' ?>>Operador</option>
                                    <option value="admin_municipal" <?= $u['rol'] === 'admin_municipal' ? 'selected' : '' ?>>Admin Municipal</option>
                                    <option value="admin_sistema" <?= $u['rol'] === 'admin_sistema' ? 'selected' : '' ?>>Admin Sistema</option>
                                </select>
                            </td>
                            <td><?= htmlspecialchars($u['distrito_nombre'] ?? '--') ?></td>
                            <td><span class="status-badge status-<?= $u['estado'] ?>"><?= ucfirst($u['estado']) ?></span></td>
                            <td><?= $u['ultimo_acceso'] ? tiempoRelativo($u['ultimo_acceso']) : 'Nunca' ?></td>
                            <td>
                                <?php if ($u['estado'] === 'activo'): ?>
                                <button class="btn btn-danger btn-sm btn-accion-usuario" data-id="<?= $u['id'] ?>" data-accion="suspender">Suspender</button>
                                <?php elseif ($u['estado'] === 'suspendido'): ?>
                                <button class="btn btn-primary btn-sm btn-accion-usuario" data-id="<?= $u['id'] ?>" data-accion="activar">Activar</button>
                                <?php endif; ?>
                                <button class="btn btn-danger btn-sm btn-accion-usuario" data-id="<?= $u['id'] ?>" data-accion="eliminar">Eliminar</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("buscarUsuario").addEventListener("input", function() {
        var q = this.value.toLowerCase();
        document.querySelectorAll("#usuariosTable tbody tr").forEach(function(row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.indexOf(q) > -1 ? "" : "none";
        });
    });
    document.querySelectorAll(".cambio-rol").forEach(function(sel) {
        sel.addEventListener("change", function() {
            var id = this.dataset.id;
            var rol = this.value;
            var fd = new FormData();
            fd.append("id", id);
            fd.append("rol", rol);
            fetch("<?= SITE_URL ?>/index.php?view=api&action=sistema_usuario_actualizar", { method: "POST", body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) { /* reload or notify */ }
                    else { alert(d.error || "Error al actualizar rol"); }
                });
        });
    });
    document.querySelectorAll(".btn-accion-usuario").forEach(function(b) {
        b.addEventListener("click", function() {
            var id = this.dataset.id;
            var accion = this.dataset.accion;
            if (accion === "eliminar" && !confirm("Eliminar este usuario? Esta accion no se puede deshacer.")) return;
            if (accion === "suspender" && !confirm("Suspender este usuario?")) return;
            var fd = new FormData();
            fd.append("id", id);
            fd.append("accion", accion);
            fetch("<?= SITE_URL ?>/index.php?view=api&action=sistema_usuario_estado", { method: "POST", body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) { location.reload(); }
                    else { alert(d.error || "Error"); }
                });
        });
    });
});
</script>
