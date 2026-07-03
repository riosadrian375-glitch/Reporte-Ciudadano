@extends('layouts.app')

@section('title', 'Panel - Reporte Ciudadano')

@section('content')
    <section class="page">
        <div class="page-head">
            <div>
                <p class="kicker">{{ auth()->user()->role }}</p>
                <h1>Panel de {{ auth()->user()->name }}</h1>
            </div>
            <div class="actions">
                <a class="button" href="{{ route($panelRoute) }}"><i data-lucide="layout-dashboard"></i>Ir a mi panel</a>
                <a class="button primary" href="{{ route('reportes.create') }}"><i data-lucide="plus"></i>Nuevo reporte</a>
            </div>
        </div>

        <div class="stats">
            <article><strong>{{ $stats['mis_reportes'] }}</strong><span>Mis reportes</span></article>
            <article><strong>{{ $stats['pendientes'] }}</strong><span>Pendientes</span></article>
            <article><strong>{{ $stats['en_proceso'] }}</strong><span>En proceso</span></article>
            <article><strong>{{ $stats['resueltos'] }}</strong><span>Resueltos</span></article>
        </div>

        <section class="info-band">
            <strong>Clima actual</strong>
            <span>{{ $climaActual['temperatura'] }}°C · {{ $climaActual['condicion'] }} · fuente {{ $climaActual['fuente'] }}</span>
        </section>

        <h2>Últimos reportes</h2>
        <div class="report-grid">
            @foreach ($reportes as $reporte)
                @include('reportes.partials.card', ['reporte' => $reporte])
            @endforeach
        </div>
    </section>
@endsection
