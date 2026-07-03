@extends('layouts.app')

@section('title', 'Panel ciudadano - Reporte Ciudadano')

@section('content')
    <section class="page">
        <div class="page-head">
            <div>
                <p class="kicker">Ciudadano</p>
                <h1>Panel de {{ auth()->user()->name }}</h1>
            </div>
            <a class="button primary" href="{{ route('reportes.create') }}"><i data-lucide="plus"></i>Nuevo reporte</a>
        </div>

        <div class="stats">
            <article><strong>{{ $stats['mis_reportes'] }}</strong><span>Mis reportes</span></article>
            <article><strong>{{ $stats['pendientes'] }}</strong><span>Pendientes</span></article>
            <article><strong>{{ $stats['en_proceso'] }}</strong><span>En proceso</span></article>
            <article><strong>{{ $stats['resueltos'] }}</strong><span>Resueltos</span></article>
        </div>

        <h2>Mis reportes</h2>
        <div class="report-grid">
            @forelse ($reportes as $reporte)
                @include('reportes.partials.card', ['reporte' => $reporte])
            @empty
                <p>Todavia no registraste reportes.</p>
            @endforelse
        </div>

        {{ $reportes->links() }}
    </section>
@endsection
