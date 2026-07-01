<section class="page-header">
    <div class="container">
        <h1 class="page-title">Crear Reporte</h1>
        <p class="page-subtitle">Reporta un problema en tu comunidad</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <form id="reporteForm" class="form-reporte" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="titulo" class="form-label">Titulo del reporte</label>
                    <input type="text" id="titulo" name="titulo" class="form-input" placeholder="Ej: Bache en la Av. Ejercito" required maxlength="200">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="categoria_id" class="form-label">Categoria</label>
                    <select id="categoria_id" name="categoria_id" class="form-input" required>
                        <option value="">Selecciona una categoria</option>
                        <?php $categorias = Categoria::listar(); foreach ($categorias as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="distrito_id" class="form-label">Distrito</label>
                    <select id="distrito_id" name="distrito_id" class="form-input" required>
                        <option value="">Selecciona un distrito</option>
                        <?php $distritos = Distrito::listar(); foreach ($distritos as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="descripcion" class="form-label">Descripcion</label>
                    <textarea id="descripcion" name="descripcion" class="form-input form-textarea" rows="5" placeholder="Describe el problema con detalle..." required maxlength="2000"></textarea>
                    <span class="form-hint">Maximo 2000 caracteres</span>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Ubicacion en el mapa</label>
                    <p class="form-hint">Arrastra el marcador para indicar la ubicacion exacta</p>
                    <?php $latitud = -16.4090; $longitud = -71.5375; $draggable = true; ?>
                    <?php require dirname(__DIR__) . '/partials/mapa.php'; ?>
                    <input type="hidden" name="latitud" id="latitud" value="-16.4090">
                    <input type="hidden" name="longitud" id="longitud" value="-71.5375">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Direccion (opcional)</label>
                    <input type="text" name="direccion" class="form-input" placeholder="Ej: Av. Ejercito cdra. 5">
                </div>
            </div>
            <div class="form-row form-row-imagenes">
                <div class="form-group">
                    <label class="form-label">Imagenes</label>
                    <div class="file-upload" id="imageUpload">
                        <div class="file-upload-placeholder">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#9E9E9E" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                            <p>Arrastra imagenes aqui o haz clic para subir</p>
                            <span class="form-hint">JPG, PNG, WEBP. Max 5MB cada una</span>
                        </div>
                        <input type="file" name="imagenes[]" id="imagenesInput" class="file-input" multiple accept="image/jpeg,image/png,image/webp">
                        <div class="file-preview" id="imagePreview"></div>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Video (opcional)</label>
                    <div class="file-upload">
                        <div class="file-upload-placeholder">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#9E9E9E" stroke-width="1.5"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
                            <p>Sube un video relacionado</p>
                            <span class="form-hint">MP4, WEBM. Max 50MB</span>
                        </div>
                        <input type="file" name="video" id="videoInput" class="file-input" accept="video/mp4,video/webm">
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group form-checkbox">
                    <label class="checkbox-label">
                        <input type="checkbox" name="es_urgente" value="1">
                        <span class="checkbox-custom"></span>
                        Marcar como urgente
                    </label>
                    <span class="form-hint">Los reportes urgentes tienen prioridad en la revision</span>
                </div>
            </div>
            <div class="form-error" id="reporteError" style="display:none"></div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    Publicar Reporte
                </button>
                <a href="<?= SITE_URL ?>/index.php?view=feed" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('reporteForm');
    var imageInput = document.getElementById('imagenesInput');
    var imagePreview = document.getElementById('imagePreview');
    imageInput.addEventListener('change', function() {
        imagePreview.innerHTML = '';
        Array.from(this.files).forEach(function(file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = document.createElement('div');
                img.className = 'file-preview-item';
                img.innerHTML = '<img src="' + e.target.result + '" alt="preview"><button type="button" class="file-preview-remove" data-file="' + file.name + '">&times;</button>';
                imagePreview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });
    imagePreview.addEventListener('click', function(e) {
        if (e.target.classList.contains('file-preview-remove')) {
            e.target.parentElement.remove();
        }
    });
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var errorEl = document.getElementById('reporteError');
        errorEl.style.display = 'none';
        var formData = new FormData(this);
        fetch('<?= SITE_URL ?>/index.php?view=api&action=reportes_crear', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.href = '<?= SITE_URL ?>/index.php?view=reporte/detalle&id=' + data.reporte_id;
            } else {
                errorEl.textContent = data.error || 'Error al crear el reporte';
                errorEl.style.display = 'block';
            }
        })
        .catch(function() {
            errorEl.textContent = 'Error de conexion. Intenta de nuevo.';
            errorEl.style.display = 'block';
        });
    });
});
</script>
