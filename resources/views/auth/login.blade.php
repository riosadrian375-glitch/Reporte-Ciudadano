@extends('layouts.app')

@section('title', 'Ingresar - Reporte Ciudadano')

@section('content')
    <section class="auth-card">
        <p class="kicker">Acceso al sistema</p>
        <h1>Iniciar sesión</h1>
        <p>Ingresa con tu correo registrado para continuar.</p>

        <form method="POST" action="{{ route('login') }}" class="form">
            @csrf
            <label>
                Correo
                <input type="email" name="email" value="{{ old('email') }}" placeholder="example@gmail.com" autocomplete="username" required autofocus>
            </label>
            <label>
                Contraseña
                <input type="password" name="password" autocomplete="current-password" required>
            </label>
            <label class="check">
                <input type="checkbox" name="remember" value="1">
                Recordarme
            </label>
            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror
            <button class="button primary submit-wide" type="submit">
                <i data-lucide="log-in"></i>Ingresar
            </button>
        </form>
    </section>
@endsection
