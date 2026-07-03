@extends('layouts.app')

@section('title', 'Admin municipal - Reporte Ciudadano')

@section('content')
    @php
        $weatherIcon = str_contains(strtolower($climaActual['condicion'] ?? ''), 'lluv') ? 'cloud-rain' : 'sun-medium';
    @endphp

    <section class="page">
        <div class="page-head">
            <div>
                <p class="kicker">Administrador municipal</p>
                <h1>Gestion municipal</h1>
            </div>
        </div>

        <div class="stats">
            <article><strong>{{ $totalReportes }}</strong><span>Total reportes</span></article>
            <article><strong>{{ $pendientes }}</strong><span>Pendientes</span></article>
            <article><strong>{{ $urgentes }}</strong><span>Urgentes</span></article>
            <article><strong>{{ $operadores }}</strong><span>Operadores</span></article>
        </div>

        <section class="weather-card">
            <span class="weather-icon"><i data-lucide="{{ $weatherIcon }}"></i></span>
            <div>
                <strong>Clima actual</strong>
                <span>{{ $climaActual['temperatura'] }} C &middot; {{ $climaActual['condicion'] }} &middot; humedad {{ $climaActual['humedad'] }}% &middot; fuente {{ $climaActual['fuente'] }}</span>
            </div>
        </section>

        <h2>Reportes recientes</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Reporte</th>
                        <th>Distrito</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reportes as $reporte)
                        <tr>
                            <td>{{ $reporte->titulo }}</td>
                            <td>{{ $reporte->distrito->nombre }}</td>
                            <td>{{ $reporte->estado }}</td>
                            <td>
                                <div class="table-actions">
                                    <a class="action-pill action-view" href="{{ route('reportes.show', $reporte) }}">
                                        <i data-lucide="eye"></i>Ver
                                    </a>
                                    <a class="action-pill action-assign" href="{{ route('reportes.asignar.create', $reporte) }}">
                                        <i data-lucide="user-plus"></i>Asignar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
