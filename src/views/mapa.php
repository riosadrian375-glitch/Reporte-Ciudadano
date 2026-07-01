<section class="mapa-fullscreen">
    <div class="mapa-header">
        <div class="container">
            <h1 class="page-title">Mapa de Reportes</h1>
            <p class="page-subtitle">Visualiza todos los reportes ciudadanos en el mapa de Arequipa</p>
        </div>
    </div>
    <div class="mapa-filtros">
        <div class="container">
            <div class="filter-chips" id="mapaFilterChips">
                <button class="chip chip-active" data-categoria="">Todas las categorias</button>
                <?php $categorias = Categoria::listar(); foreach ($categorias as $c): ?>
                <button class="chip" data-categoria="<?= $c['id'] ?>">
                    <?php if (!empty($c['icono'])): ?><span class="chip-icon"><?= htmlspecialchars($c['icono']) ?></span><?php endif; ?>
                    <?= htmlspecialchars($c['nombre']) ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div id="fullscreenMap" class="mapa-container-full" style="height: 600px; width: 100%;"></div>
</section>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var map = L.map("fullscreenMap").setView([-16.4090, -71.5375], 13);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap contributors",
        maxZoom: 19
    }).addTo(map);
    var markers = [];
    var allReportes = [];
    var markerGroup = L.layerGroup().addTo(map);
    function cargarReportes() {
        fetch("<?= SITE_URL ?>/index.php?view=api&action=mapa_reportes")
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success && data.reportes) {
                    allReportes = data.reportes;
                    mostrarReportes(allReportes);
                }
            });
    }
    function mostrarReportes(reportes) {
        markerGroup.clearLayers();
        markers = [];
        reportes.forEach(function(r) {
            if (!r.latitud || !r.longitud) return;
            var iconColor = r.es_urgente ? "#D32F2F" : "#0A0A0A";
            var icon = L.divIcon({
                className: "marker-reporte",
                html: "<div style=\"background:" + iconColor + ";color:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.3)\">" + (r.categoria_icono || "!") + "</div>",
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });
            var marker = L.marker([r.latitud, r.longitud], { icon: icon })
                .bindPopup("<strong><a href=\"<?= SITE_URL ?>/index.php?view=reporte/detalle&id=" + r.id + "\">" + r.titulo + "</a></strong><br>" + (r.categoria_nombre || "") + " &middot; " + (r.distrito_nombre || ""));
            markerGroup.addLayer(marker);
            markers.push(marker);
        });
    }
    cargarReportes();
    document.querySelectorAll("#mapaFilterChips .chip").forEach(function(chip) {
        chip.addEventListener("click", function() {
            document.querySelectorAll("#mapaFilterChips .chip").forEach(function(c) { c.classList.remove("chip-active"); });
            this.classList.add("chip-active");
            var catId = this.dataset.categoria;
            var filtered = catId ? allReportes.filter(function(r) { return r.categoria_id == catId; }) : allReportes;
            mostrarReportes(filtered);
        });
    });
});
</script>
