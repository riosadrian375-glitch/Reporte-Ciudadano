@extends('layouts.app')

@section('title', 'Reporte Ciudadano - Arequipa')

@section('content')
    <section class="home-portal">
        <div class="portal-story">
            <div class="portal-copy">
                <p class="kicker">Ciudad del Misti</p>
                <h1>Reporta incidencias.<br>Mejora tu ciudad.</h1>
                <p class="lead">Un espacio ciudadano para comunicar problemas urbanos, seguir su atencion y apoyar una gestion municipal mas ordenada.</p>

                <div class="portal-metrics" aria-label="Indicadores de la plataforma">
                    <span><strong>21</strong> distritos conectados</span>
                    <span><strong>24/7</strong> participacion activa</span>
                </div>
            </div>
        </div>

        <div class="portal-access" id="acceso">
            @auth
                <div class="portal-brand-lockup">
                    <img src="{{ asset('assets/img/reporte-ciudadano-ico.png') }}" alt="Reporte Ciudadano">
                    <span>Reporte <strong>Ciudadano</strong><small>Arequipa participa</small></span>
                </div>
                <p class="kicker">Sesion activa</p>
                <h2>Continua gestionando tu ciudad</h2>
                <p class="portal-access-copy">Ya ingresaste al sistema. Desde tu panel puedes revisar reportes, registrar incidencias y dar seguimiento a las acciones municipales.</p>
                <div class="actions">
                    <a class="button primary" href="{{ route('dashboard') }}"><i data-lucide="layout-dashboard"></i>Ir al panel</a>
                    <a class="button" href="{{ route('reportes.create') }}"><i data-lucide="plus"></i>Nuevo reporte</a>
                </div>
            @else
                <div class="portal-brand-lockup">
                    <img src="{{ asset('assets/img/reporte-ciudadano-ico.png') }}" alt="Reporte Ciudadano">
                    <span>Reporte <strong>Ciudadano</strong><small>Arequipa participa</small></span>
                </div>

                <div class="auth-switch" aria-label="Acceso ciudadano">
                    <input type="radio" name="portal_auth" id="portal-login-tab" checked>
                    <input type="radio" name="portal_auth" id="portal-register-tab">

                    <div class="auth-tabs">
                        <label for="portal-login-tab"><i data-lucide="log-in"></i>Login</label>
                        <label for="portal-register-tab"><i data-lucide="user-plus"></i>Registro</label>
                    </div>

                    <div class="auth-panel login-panel">
                        <p class="kicker">Bienvenido de nuevo</p>
                        <h2>Iniciar sesion</h2>
                        <p class="portal-access-copy">Accede a tu panel y sigue mejorando tu ciudad.</p>

                        <form method="POST" action="{{ route('login') }}" class="form portal-form">
                            @csrf
                            <label>
                                Correo electronico
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="tu@correo.com" autocomplete="username" required>
                            </label>
                            <label>
                                Contrasena
                                <input type="password" name="password" placeholder="********" autocomplete="current-password" required>
                            </label>
                            <label class="check portal-check">
                                <input type="checkbox" name="remember" value="1">
                                Mantener sesion iniciada
                            </label>
                            @error('email')
                                <p class="error">{{ $message }}</p>
                            @enderror
                            <button class="button primary submit-wide" type="submit">
                                <i data-lucide="log-in"></i>Ingresar a Reporte Ciudadano
                            </button>
                        </form>
                    </div>

                    <div class="auth-panel register-panel">
                        <p class="kicker">Participacion ciudadana</p>
                        <h2>Crear cuenta</h2>
                        <p class="portal-access-copy">Registrate para reportar incidencias y consultar el avance de tus solicitudes.</p>

                        <form method="POST" action="{{ route('register') }}" class="form portal-form">
                            @csrf
                            <label>
                                Nombre completo
                                <input type="text" name="name" value="{{ old('name') }}" placeholder="Tu nombre" autocomplete="name" required>
                                @error('name') <span class="error">{{ $message }}</span> @enderror
                            </label>
                            <label>
                                Correo electronico
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="tu@correo.com" autocomplete="email" required>
                                @error('email') <span class="error">{{ $message }}</span> @enderror
                            </label>
                            <div class="form-grid">
                                <label>
                                    Contrasena
                                    <input type="password" name="password" placeholder="Minimo 8 caracteres" autocomplete="new-password" required>
                                    @error('password') <span class="error">{{ $message }}</span> @enderror
                                </label>
                                <label>
                                    Confirmar contrasena
                                    <input type="password" name="password_confirmation" placeholder="Repite tu contrasena" autocomplete="new-password" required>
                                </label>
                            </div>
                            <button class="button primary submit-wide" type="submit">
                                <i data-lucide="user-plus"></i>Crear cuenta ciudadana
                            </button>
                        </form>
                    </div>
                </div>

                <details class="demo-accounts">
                    <summary>Ver cuentas de demostracion</summary>
                    <div>
                        <span>Ciudadano: carlos@example.com</span>
                        <span>Admin municipal: admin@municipal.gob.pe</span>
                        <span>Operador: operador1@municipal.gob.pe</span>
                        <span>Contrasena: test1234</span>
                    </div>
                </details>
            @endauth
        </div>
    </section>
@endsection
