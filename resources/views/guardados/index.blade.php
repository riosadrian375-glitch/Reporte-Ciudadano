@extends('layouts.app')

@section('title', 'Guardados - Reporte Ciudadano')

@section('content')
    <section class="page">
        <div class="page-head">
            <div>
                <p class="kicker">Reportes guardados</p>
                <h1>Guardados</h1>
            </div>
        </div>

        <div class="report-grid">
            @forelse ($reportes as $reporte)
                @include('reportes.partials.card', ['reporte' => $reporte])
            @empty
                <p>No guardaste reportes todavia.</p>
            @endforelse
        </div>

        {{ $reportes->links() }}
    </section>
@endsection
