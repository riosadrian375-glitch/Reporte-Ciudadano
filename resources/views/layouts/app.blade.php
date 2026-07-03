<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Reporte Ciudadano')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('head')
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('home') }}" aria-label="Inicio Reporte Ciudadano">
            <img src="{{ asset('assets/img/reporte-ciudadano-ico.png') }}" alt="Reporte Ciudadano">
            <span class="brand-copy">Reporte<span>Ciudadano</span><small>Arequipa participa</small></span>
        </a>

        <span class="flag-pill"><i data-lucide="map-pin"></i>Ciudad del Misti</span>

        <nav class="nav">
            @auth
                @php
                    $panelRoute = match (auth()->user()->role) {
                        'admin_municipal' => 'panel.admin-municipal',
                        'operador' => 'panel.operador',
                        'admin_sistema' => 'panel.admin-sistema',
                        default => 'panel.ciudadano',
                    };
                @endphp
                <details class="nav-menu">
                    <summary>
                        <i data-lucide="menu"></i>
                        <span>Menu</span>
                    </summary>
                    <div class="nav-dropdown">
                        <a href="{{ route('reportes.index') }}"><i data-lucide="clipboard-list"></i>Reportes</a>
                        <a href="{{ route('mapa.index') }}"><i data-lucide="map"></i>Mapa</a>
                        <a href="{{ route('emergencias.index') }}"><i data-lucide="siren"></i>Emergencias</a>
                        <a href="{{ route('notificaciones.index') }}"><i data-lucide="bell"></i>Notificaciones</a>
                        <a href="{{ route('profile.show') }}"><i data-lucide="user-round"></i>Perfil</a>
                        <a href="{{ route('guardados.index') }}"><i data-lucide="bookmark"></i>Guardados</a>
                        <a href="{{ route($panelRoute) }}"><i data-lucide="layout-dashboard"></i>Panel</a>
                        <button type="button" class="theme-toggle" data-theme-toggle>
                            <i data-lucide="moon"></i>
                            <span>Modo oscuro</span>
                        </button>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"><i data-lucide="log-out"></i>Salir</button>
                        </form>
                    </div>
                </details>
            @endauth
        </nav>
    </header>

    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif

    <main class="main-shell">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div>
            <strong>Reporte Ciudadano</strong>
            <span>Proyecto integrador desarrollado para Tecsup y orientado a la Municipalidad Provincial de Arequipa.</span>
        </div>
        <div>
            <span>Instituto Tecsup</span>
            <span>Municipalidad de Arequipa</span>
            <span>2026</span>
        </div>
    </footer>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        const savedTheme = localStorage.getItem('reporte-theme') || 'light';
        document.documentElement.dataset.theme = savedTheme;

        function syncThemeToggle() {
            const isDark = document.documentElement.dataset.theme === 'dark';

            document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
                const icon = button.querySelector('i');
                const label = button.querySelector('span');

                if (icon) {
                    icon.setAttribute('data-lucide', isDark ? 'sun' : 'moon');
                }

                if (label) {
                    label.textContent = isDark ? 'Modo claro' : 'Modo oscuro';
                }
            });
        }

        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';

                document.documentElement.dataset.theme = nextTheme;
                localStorage.setItem('reporte-theme', nextTheme);
                syncThemeToggle();

                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        });

        syncThemeToggle();

        if (window.lucide) {
            window.lucide.createIcons();
        }
    </script>
    @yield('scripts')
</body>
</html>
