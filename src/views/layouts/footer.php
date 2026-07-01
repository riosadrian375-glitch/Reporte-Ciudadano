    </main>

    <footer class="footer-principal">
        <div class="container footer-container">
            <div class="footer-brand">
                <img src="<?= SITE_URL ?>/assets/img/escudo-arequipa.png" alt="" class="footer-seal">
                <div>
                    <p>&copy; <?= date('Y') ?> <strong>ReporteCiudadano</strong> &mdash; Arequipa, Perú</p>
                    <p class="footer-tagline">Participación ciudadana desde la ciudad del Misti</p>
                </div>
            </div>
            <div class="footer-links">
                <a href="<?= SITE_URL ?>/index.php?view=emergencias" class="footer-link">Emergencias</a>
                <a href="<?= SITE_URL ?>/index.php?view=mapa" class="footer-link">Mapa</a>
                <a href="#" class="footer-link">Acerca de</a>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
