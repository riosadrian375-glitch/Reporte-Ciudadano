<div class="clima-widget" id="climaWidget">
    <div class="clima-loading">
        <span class="loader-sm"></span>
        <span>Cargando clima...</span>
    </div>
    <div class="clima-content" style="display:none">
        <div class="clima-header">
            <span class="clima-icon" id="climaIcon"></span>
            <span class="clima-temp" id="climaTemp">--°C</span>
        </div>
        <div class="clima-info">
            <span class="clima-desc" id="climaDesc">--</span>
            <span class="clima-location" id="climaLocation">Arequipa</span>
            <div class="clima-details">
                <span>Humedad: <strong id="climaHumidity">--%</strong></span>
                <span>Viento: <strong id="climaWind">-- km/h</strong></span>
            </div>
        </div>
    </div>
    <div class="clima-error" style="display:none; color: var(--acento-rojo-institucional); font-size: 0.85rem;">
        No se pudo cargar el clima
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var distritoId = <?= json_encode($_SESSION['usuario_distrito'] ?? 'null') ?>;
    var url = '<?= SITE_URL ?>/index.php?view=api&action=clima_obtener';
    if (distritoId) url += '&distrito_id=' + distritoId;
    fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.clima) {
                var c = data.clima;
                document.getElementById('climaTemp').textContent = c.temperatura ? c.temperatura + '°C' : '--°C';
                document.getElementById('climaDesc').textContent = c.descripcion || '--';
                document.getElementById('climaLocation').textContent = c.ciudad || 'Arequipa';
                document.getElementById('climaHumidity').textContent = c.humedad ? c.humedad + '%' : '--%';
                document.getElementById('climaWind').textContent = c.viento ? c.viento + ' km/h' : '-- km/h';
                if (c.icono) document.getElementById('climaIcon').innerHTML = '<img src="' + c.icono + '" alt="icono clima" width="40" height="40">';
                document.querySelector('.clima-loading').style.display = 'none';
                document.querySelector('.clima-content').style.display = 'flex';
            } else {
                document.querySelector('.clima-loading').style.display = 'none';
                document.querySelector('.clima-error').style.display = 'block';
            }
        })
        .catch(function() {
            document.querySelector('.clima-loading').style.display = 'none';
            document.querySelector('.clima-error').style.display = 'block';
        });
});
</script>
