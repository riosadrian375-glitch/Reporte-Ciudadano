@extends('layouts.app')

@section('title', 'Registro - Reporte Ciudadano')

@section('content')
    <section class="auth-card">
        <p class="kicker">Participación ciudadana</p>
        <h1>Crear cuenta ciudadana</h1>

        <form method="POST" action="{{ route('register') }}" class="form">
            @csrf
            <label>
                Nombre
                <input type="text" name="name" value="{{ old('name') }}" required>
                @error('name') <span class="error">{{ $message }}</span> @enderror
            </label>
            <label>
                Correo
                <input type="email" name="email" value="{{ old('email') }}" required>
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </label>
            <label>
                Contraseña
                <input type="password" name="password" required>
                @error('password') <span class="error">{{ $message }}</span> @enderror
            </label>
            <label>
                Confirmar contraseña
                <input type="password" name="password_confirmation" required>
            </label>
            <button class="button primary" type="submit">Registrarme</button>
        </form>
    </section>
@endsection
