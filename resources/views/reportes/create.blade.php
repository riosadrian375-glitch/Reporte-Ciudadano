@extends('layouts.app')

@section('title', 'Nuevo reporte - Reporte Ciudadano')

@section('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endsection

@section('content')
    <section class="page narrow">
        <p class="kicker">Registro ciudadano</p>
        <h1>Registrar nuevo reporte</h1>

        <form method="POST" action="{{ route('reportes.store') }}" class="form report-form" enctype="multipart/form-data" id="reportForm">
            @csrf
            <label>
                Título
                <input type="text" name="titulo" value="{{ old('titulo') }}" required>
                @error('titulo') <span class="error">{{ $message }}</span> @enderror
            </label>

            <div class="form-grid">
                <label>
                    Categoría
                    <select name="categoria_id" required>
                        <option value="">Selecciona una categoría</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected(old('categoria_id') == $categoria->id)>{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_id') <span class="error">{{ $message }}</span> @enderror
                </label>

                <label>
                    Distrito
                    <select name="distrito_id" required>
                        <option value="">Selecciona un distrito</option>
                        @foreach ($distritos as $distrito)
                            <option value="{{ $distrito->id }}" @selected(old('distrito_id') == $distrito->id)>{{ $distrito->nombre }}</option>
                        @endforeach
                    </select>
                    @error('distrito_id') <span class="error">{{ $message }}</span> @enderror
                </label>
            </div>

            <label>
                Dirección referencial
                <input type="text" name="direccion" value="{{ old('direccion') }}" placeholder="Ej. Av. Ejército con Cayma">
            </label>

            <div class="location-picker-panel">
                <div class="location-header">
                    <div>
                        <strong>Ubicación del reporte</strong>
                        <span>Haz clic en el mapa o arrastra el marcador hasta el lugar exacto.</span>
                    </div>
                    <button class="button ghost" type="button" id="useCurrentLocation">
                        <i data-lucide="locate-fixed"></i>Usar mi ubicación
                    </button>
                </div>
                <div id="reportLocationMap" class="map location-picker"></div>
                <p class="location-readout" id="locationReadout">Ubicación seleccionada: Arequipa centro</p>
                <input type="hidden" name="latitud" id="latitud" value="{{ old('latitud', '-16.3989000') }}">
                <input type="hidden" name="longitud" id="longitud" value="{{ old('longitud', '-71.5350000') }}">
                @error('latitud') <span class="error">{{ $message }}</span> @enderror
                @error('longitud') <span class="error">{{ $message }}</span> @enderror
            </div>

            <label>
                Descripción
                <textarea name="descripcion" rows="5" required>{{ old('descripcion') }}</textarea>
                @error('descripcion') <span class="error">{{ $message }}</span> @enderror
            </label>

            <label class="check">
                <input type="checkbox" name="es_urgente" value="1" @checked(old('es_urgente'))>
                Marcar como urgente
            </label>

            <section class="media-uploader">
                <div>
                    <strong>Evidencias</strong>
                    <span>Agrega imágenes y videos cortos. Los videos no deben superar 1 minuto.</span>
                </div>

                <div class="upload-grid">
                    <label class="upload-box">
                        <i data-lucide="image-plus"></i>
                        <strong>Imágenes</strong>
                        <span>JPG, PNG o WEBP</span>
                        <input type="file" name="imagenes[]" accept="image/jpeg,image/png,image/webp" multiple>
                    </label>

                    <label class="upload-box">
                        <i data-lucide="video"></i>
                        <strong>Videos cortos</strong>
                        <span>MP4, MOV o WEBM. Máx. 60 segundos.</span>
                        <input type="file" name="videos[]" id="videoInput" accept="video/mp4,video/quicktime,video/webm" multiple>
                    </label>
                </div>

                <div id="videoDurations"></div>
                <p class="upload-feedback" id="videoFeedback"></p>
                @error('imagenes.*') <span class="error">{{ $message }}</span> @enderror
                @error('videos.*') <span class="error">{{ $message }}</span> @enderror
                @error('video_durations.*') <span class="error">{{ $message }}</span> @enderror
            </section>

            <button class="button primary submit-wide" type="submit">
                <i data-lucide="send"></i>Guardar reporte
            </button>
        </form>
    </section>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const latInput = document.getElementById('latitud');
        const lngInput = document.getElementById('longitud');
        const readout = document.getElementById('locationReadout');
        const startLat = Number(latInput.value || -16.3989);
        const startLng = Number(lngInput.value || -71.5350);

        const reportMap = L.map('reportLocationMap').setView([startLat, startLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(reportMap);

        const marker = L.marker([startLat, startLng], { draggable: true }).addTo(reportMap);

        function setLocation(lat, lng, zoom = false) {
            const cleanLat = Number(lat).toFixed(7);
            const cleanLng = Number(lng).toFixed(7);
            latInput.value = cleanLat;
            lngInput.value = cleanLng;
            marker.setLatLng([cleanLat, cleanLng]);
            readout.textContent = `Ubicación seleccionada: ${cleanLat}, ${cleanLng}`;

            if (zoom) {
                reportMap.setView([cleanLat, cleanLng], 16);
            }
        }

        setLocation(startLat, startLng);
        reportMap.on('click', (event) => setLocation(event.latlng.lat, event.latlng.lng));
        marker.on('dragend', () => {
            const position = marker.getLatLng();
            setLocation(position.lat, position.lng);
        });

        document.getElementById('useCurrentLocation').addEventListener('click', () => {
            if (!navigator.geolocation) {
                readout.textContent = 'Tu navegador no permite obtener la ubicación automáticamente.';
                return;
            }

            readout.textContent = 'Buscando tu ubicación...';
            navigator.geolocation.getCurrentPosition(
                (position) => setLocation(position.coords.latitude, position.coords.longitude, true),
                () => readout.textContent = 'No se pudo obtener tu ubicación. Puedes marcarla manualmente en el mapa.',
                { enableHighAccuracy: true, timeout: 8000 }
            );
        });

        const videoInput = document.getElementById('videoInput');
        const videoDurations = document.getElementById('videoDurations');
        const videoFeedback = document.getElementById('videoFeedback');
        let videosValid = true;

        videoInput.addEventListener('change', async () => {
            videoDurations.innerHTML = '';
            videoFeedback.textContent = '';
            videosValid = true;

            const files = Array.from(videoInput.files);
            for (const [index, file] of files.entries()) {
                const duration = await readVideoDuration(file);
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `video_durations[${index}]`;
                input.value = duration ? Math.round(duration) : '';
                videoDurations.appendChild(input);

                if (duration && duration > 60) {
                    videosValid = false;
                    videoFeedback.textContent = `El video "${file.name}" dura más de 1 minuto. Selecciona un video más corto.`;
                }
            }

            if (files.length && videosValid) {
                videoFeedback.textContent = `${files.length} video(s) listo(s) para subir.`;
            }
        });

        document.getElementById('reportForm').addEventListener('submit', (event) => {
            if (!videosValid) {
                event.preventDefault();
                videoFeedback.textContent = 'Corrige los videos antes de guardar el reporte.';
            }
        });

        function readVideoDuration(file) {
            return new Promise((resolve) => {
                const video = document.createElement('video');
                video.preload = 'metadata';
                video.onloadedmetadata = () => {
                    URL.revokeObjectURL(video.src);
                    resolve(video.duration);
                };
                video.onerror = () => resolve(null);
                video.src = URL.createObjectURL(file);
            });
        }
    </script>
@endsection
