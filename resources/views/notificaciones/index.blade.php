@extends('layouts.app')

@section('title', 'Notificaciones - Reporte Ciudadano')

@section('content')
    <section class="page">
        <div class="page-head">
            <div>
                <p class="kicker">Centro de avisos</p>
                <h1>Notificaciones</h1>
            </div>
            <form method="POST" action="{{ route('notificaciones.mark-all') }}">
                @csrf
                <button class="button" type="submit">Marcar todas como leidas</button>
            </form>
        </div>

        <div class="notification-list">
            @forelse ($notificaciones as $notificacion)
                <article class="notification @if(! $notificacion->leida) unread @endif">
                    <div>
                        <strong>{{ ucfirst($notificacion->tipo) }}</strong>
                        <p>{{ $notificacion->mensaje }}</p>
                        <small>{{ $notificacion->created_at->diffForHumans() }}</small>
                    </div>
                    @if ($notificacion->referencia_id)
                        <a class="button" href="{{ route('reportes.show', $notificacion->referencia_id) }}">Ver reporte</a>
                    @endif
                </article>
            @empty
                <p>No tienes notificaciones.</p>
            @endforelse
        </div>

        {{ $notificaciones->links() }}
    </section>
@endsection
