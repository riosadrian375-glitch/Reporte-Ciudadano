@extends('layouts.app')

@section('title', 'Panel operador - Reporte Ciudadano')

@section('content')
    @php
        $weatherIcon = str_contains(strtolower($climaActual['condicion'] ?? ''), 'lluv') ? 'cloud-rain' : 'sun-medium';
    @endphp

    <section class="page">
        <div class="page-head">
            <div>
                <p class="kicker">Operador municipal</p>
                <h1>Panel de Operador Municipal</h1>
            </div>
        </div>

        <form method="GET" action="{{ route('panel.operador') }}" class="filters panel-filters">
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
                @foreach (['pendiente', 'en_proceso', 'resuelto'] as $estado)
                    <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>
                @endforeach
            </select>
            <button class="button" type="submit"><i data-lucide="search"></i>Filtrar</button>
        </form>

        <div class="stats compact-stats">
            <article><strong>{{ $pendientes }}</strong><span>Pendientes</span></article>
            <article><strong>{{ $enProceso }}</strong><span>En proceso</span></article>
            <article><strong>{{ $resueltos }}</strong><span>Resueltos</span></article>
        </div>

        <section class="weather-card">
            <span class="weather-icon"><i data-lucide="{{ $weatherIcon }}"></i></span>
            <div>
                <strong>Clima actual</strong>
                <span>{{ $climaActual['temperatura'] }} C &middot; {{ $climaActual['condicion'] }} &middot; humedad {{ $climaActual['humedad'] }}% &middot; fuente {{ $climaActual['fuente'] }}</span>
            </div>
        </section>

        <h2>Reportes por atender</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Reporte</th>
                        <th>Categoria</th>
                        <th>Distrito</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportes as $reporte)
                        <tr>
                            <td>{{ $reporte->titulo }}</td>
                            <td>{{ $reporte->categoria->nombre }}</td>
                            <td>{{ $reporte->distrito->nombre }}</td>
                            <td>{{ $reporte->estado }}</td>
                            <td>
                                <div class="table-actions">
                                    <a class="action-pill action-view" href="{{ route('reportes.show', $reporte) }}">
                                        <i data-lucide="eye"></i>Ver
                                    </a>
                                    <form method="POST" action="{{ route('reportes.estado.update', $reporte) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="estado" value="resuelto">
                                        <button class="action-pill action-resolve" type="submit">
                                            <i data-lucide="check-circle"></i>Resolver
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No hay reportes pendientes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $reportes->links() }}
    </section>
@endsection
