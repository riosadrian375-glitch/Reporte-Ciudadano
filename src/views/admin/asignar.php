<?php
redirigirSiNoEsRol('admin_municipal');
$reporteId = (int)($_GET['reporte_id'] ?? 0);
$reporte = $reporteId ? Reporte::porId($reporteId) : null;
$operadores = Usuario::listar('operador');
?>
<section class="page-header">
    <div class="container">
        <h1 class="page-title">Asignar Reporte</h1>
        <p class="page-subtitle">Asigna un reporte a un operador tecnico</p>
    </div>
</section>
<section class="page-content">
    <div class="container">
        <?php if ($reporte): ?>
        <div class="card" style="max-width:600px;margin:0 auto">
            <div class="card-body">
                <h3><?= htmlspecialchars($reporte['titulo']) ?></h3>
                <p><?= htmlspecialchars(resumirTexto($reporte['descripcion'], 200)) ?></p>
                <div class="card-meta">
                    <span class="chip"><?= htmlspecialchars($reporte['categoria_nombre']) ?></span>
                    <span class="chip"><?= htmlspecialchars($reporte['distrito_nombre']) ?></span>
                    <span class="status-badge status-<?= $reporte['estado'] ?>"><?= ucfirst(str_replace('_', ' ', $reporte['estado'])) ?></span>
                </div>
                <form id="asignarForm" class="form-asignar">
                    <input type="hidden" name="reporte_id" value="<?= $reporteId ?>">
                    <div class="form-group">
                        <label class="form-label">Seleccionar Operador</label>
                        <select name="operador_id" class="form-input" required>
                            <option value="">-- Selecciona un operador --</option>
                            <?php foreach ($operadores as $op): ?>
                            <option value="<?= $op['id'] ?>"><?= htmlspecialchars($op['nombre']) ?> (<?= htmlspecialchars($op['correo']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-error" id="asignarFormError" style="display:none"></div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Asignar Reporte</button>
                        <a href="<?= SITE_URL ?>/index.php?view=admin/dashboard" class="btn btn-outline">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="card" style="max-width:600px;margin:0 auto">
            <div class="card-body">
                <h3>Seleccionar Reporte</h3>
                <?php $reportes = Reporte::listarTodosAdmin(['estado' => 'pendiente']); ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>ID</th><th>Titulo</th><th>Distrito</th><th>Accion</th></tr></thead>
                        <tbody>
                            <?php foreach ($reportes as $r): ?>
                            <tr>
                                <td><?= $r['id'] ?></td>
                                <td><?= htmlspecialchars($r['titulo']) ?></td>
                                <td><?= htmlspecialchars($r['distrito_nombre']) ?></td>
                                <td><a href="<?= SITE_URL ?>/index.php?view=admin/asignar&reporte_id=<?= $r['id'] ?>" class="btn btn-primary btn-sm">Asignar</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (empty($reportes)): ?>
                <p>No hay reportes pendientes para asignar.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var form = document.getElementById("asignarForm");
    if (form) {
        form.addEventListener("submit", function(e) {
            e.preventDefault();
            var fd = new FormData(this);
            fetch("<?= SITE_URL ?>/index.php?view=api&action=admin_asignar", { method: "POST", body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) {
                        window.location.href = "<?= SITE_URL ?>/index.php?view=admin/dashboard";
                    } else {
                        document.getElementById("asignarFormError").textContent = d.error || "Error al asignar";
                        document.getElementById("asignarFormError").style.display = "block";
                    }
                });
        });
    }
});
</script>
