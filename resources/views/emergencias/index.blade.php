@extends('layouts.app')

@section('title', 'Emergencias - Reporte Ciudadano')

@section('content')
    <section class="page">
        <div class="page-head">
            <div>
                <p class="kicker">Contactos importantes</p>
                <h1>Emergencias</h1>
            </div>
        </div>

        <div class="report-grid">
            @forelse ($contactos as $contacto)
                <article class="report-card">
                    <h3>{{ $contacto->nombre_servicio }}</h3>
                    <p>{{ $contacto->descripcion }}</p>
                    <a class="button primary" href="tel:{{ $contacto->numero }}">{{ $contacto->numero }}</a>
                </article>
            @empty
                <p>No hay contactos de emergencia configurados.</p>
            @endforelse
        </div>
    </section>
@endsection
