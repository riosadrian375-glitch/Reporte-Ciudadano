@extends('layouts.app')

@section('title', 'Reportes ciudadanos - Reporte Ciudadano')

@section('content')
    @php
        $weatherIcon = str_contains(strtolower($climaActual['condicion'] ?? ''), 'lluv') ? 'cloud-rain' : 'sun-medium';
        $panelRoute = match (auth()->user()?->role) {
            'admin_municipal' => 'panel.admin-municipal',
            'operador' => 'panel.operador',
            'admin_sistema' => 'panel.admin-sistema',
            default => 'panel.ciudadano',
        };
    @endphp

    <section class="page">
        <div class="page-head">
            <div>
                <p class="kicker">Incidencias registradas</p>
                <h1>Reportes ciudadanos</h1>
            </div>
            @auth
                <div class="actions">
                    <a class="button" href="{{ route($panelRoute) }}"><i data-lucide="layout-dashboard"></i>Ir a mi panel</a>
                    @if (auth()->user()->role === 'ciudadano')
                        <a class="button primary" href="{{ route('reportes.create') }}"><i data-lucide="plus"></i>Nuevo reporte</a>
                    @endif
                </div>
            @endauth
        </div>

        <form method="GET" action="{{ route('reportes.index') }}" class="filters">
            <input type="search" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar reportes">
            <select name="categoria_id">
                <option value="">Todas las categorias</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}" @selected(request('categoria_id') == $categoria->id)>{{ $categoria->nombre }}</option>
                @endforeach
            </select>
            <select name="distrito_id">
                <option value="">Todos los distritos</option>
                @foreach ($distritos as $distrito)
                    <option value="{{ $distrito->id }}" @selected(request('distrito_id') == $distrito->id)>{{ $distrito->nombre }}</option>
                @endforeach
            </select>
            <select name="estado">
                <option value="">Todos los estados</option>
                @foreach (['pendiente', 'en_proceso', 'resuelto', 'rechazado'] as $estado)
                    <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>
                @endforeach
            </select>
            <button class="button" type="submit"><i data-lucide="search"></i>Filtrar</button>
        </form>

        <section class="weather-card">
            <span class="weather-icon"><i data-lucide="{{ $weatherIcon }}"></i></span>
            <div>
                <strong>Clima actual</strong>
                <span>{{ $climaActual['temperatura'] }} C &middot; {{ $climaActual['condicion'] }} &middot; humedad {{ $climaActual['humedad'] }}% &middot; fuente {{ $climaActual['fuente'] }}</span>
            </div>
        </section>

        <div class="report-grid">
            @forelse ($reportes as $reporte)
                @include('reportes.partials.card', ['reporte' => $reporte])
            @empty
                <p>No hay reportes registrados todavia.</p>
            @endforelse
        </div>

        {{ $reportes->links() }}
    </section>
@endsection
