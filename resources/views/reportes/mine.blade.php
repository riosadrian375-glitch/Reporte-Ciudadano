@extends('layouts.app')

@section('title', 'Mis reportes - Reporte Ciudadano')

@section('content')
    <section class="page">
        <div class="page-head">
            <div>
                <p class="kicker">Historial ciudadano</p>
                <h1>Mis reportes</h1>
            </div>
            @if (auth()->user()->role === 'ciudadano')
                <a class="button primary" href="{{ route('reportes.create') }}">Nuevo reporte</a>
            @endif
        </div>

        <div class="report-grid">
            @forelse ($reportes as $reporte)
                @include('reportes.partials.card', ['reporte' => $reporte])
            @empty
                <p>No tienes reportes todavia.</p>
            @endforelse
        </div>

        {{ $reportes->links() }}
    </section>
@endsection
