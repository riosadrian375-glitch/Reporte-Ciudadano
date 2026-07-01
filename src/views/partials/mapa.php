<div id="mapa-reporte" class="mapa-container" style="height: 400px; width: 100%; border-radius: 8px; border: 1px solid #E0E0E0;"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var lat = <?= json_encode($latitud ?? -16.4090) ?>;
    var lng = <?= json_encode($longitud ?? -71.5375) ?>;
    var map = L.map('mapa-reporte').setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    var marker = L.marker([lat, lng], { draggable: <?= isset($draggable) && $draggable ? 'true' : 'false' ?> }).addTo(map);
    <?php if (isset($draggable) && $draggable): ?>
    marker.on('dragend', function(e) {
        var pos = e.target.getLatLng();
        document.getElementById('latitud').value = pos.lat;
        document.getElementById('longitud').value = pos.lng;
    });
    <?php endif; ?>
    <?php if (!empty($reportes_mapa)): foreach ($reportes_mapa as $rm): ?>
    L.marker([<?= $rm['latitud'] ?>, <?= $rm['longitud'] ?>])
        .addTo(map)
        .bindPopup('<strong><?= htmlspecialchars($rm['titulo']) ?></strong><br><?= htmlspecialchars($rm['categoria_nombre'] ?? '') ?>');
    <?php endforeach; endif; ?>
    window.reporteMap = map;
});
</script>
