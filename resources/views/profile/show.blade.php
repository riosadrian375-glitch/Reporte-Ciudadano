@extends('layouts.app')

@section('title', 'Mi perfil - Reporte Ciudadano')

@section('content')
    <section class="page narrow">
        <div class="profile-card">
            <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <h1>{{ $user->name }}</h1>
            <p>{{ $user->email }}</p>
            <div class="stats">
                <article><strong>{{ $totalReportes }}</strong><span>Reportes</span></article>
                <article><strong>{{ $totalLikes }}</strong><span>Likes recibidos</span></article>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="form">
            @csrf
            @method('PUT')
            <label>
                Nombre
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </label>
            <label>
                Distrito
                <select name="district_id">
                    <option value="">Sin distrito</option>
                    @foreach ($distritos as $distrito)
                        <option value="{{ $distrito->id }}" @selected(old('district_id', $user->district_id) == $distrito->id)>{{ $distrito->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <button class="button primary" type="submit">Guardar cambios</button>
        </form>
    </section>
@endsection
