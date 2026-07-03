@extends('layouts.app')

@section('title', 'Mapa de reportes - Reporte Ciudadano')

@section('content')
    <section class="page">
        <div class="page-head">
            <div>
                <p class="kicker">Geolocalizacion</p>
                <h1>Mapa de reportes</h1>
            </div>
            @if (auth()->user()->role === 'ciudadano')
                <a class="button primary" href="{{ route('reportes.create') }}">Nuevo reporte</a>
            @endif
        </div>

        <div id="map" class="map"></div>
    </section>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([-16.3989, -71.5350], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        fetch('{{ route('api.reportes-mapa') }}')
            .then(response => response.json())
            .then(reportes => {
                reportes.forEach(reporte => {
                    L.marker([reporte.latitud, reporte.longitud])
                        .addTo(map)
                        .bindPopup(`
                            <strong>${reporte.titulo}</strong><br>
                            ${reporte.categoria} - ${reporte.distrito}<br>
                            Estado: ${reporte.estado}<br>
                            <a href="${reporte.url}">Ver detalle</a>
                        `);
                });
            });
    </script>
@endsection
