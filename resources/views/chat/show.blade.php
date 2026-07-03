@extends('layouts.app')

@section('title', 'Chat IA - Reporte Ciudadano')

@section('content')
    <section class="page narrow chat-page">
        <p class="kicker">Asistente de soporte</p>
        <h1>Chat IA ciudadano</h1>
        <p class="small-lead">Consulta sobre el proyecto, el uso del sistema, estados, evidencias, roles o este reporte.</p>

        <article class="chat-context">
            <span>Reporte actual</span>
            <strong>{{ $reporte->titulo }}</strong>
            <p>{{ $reporte->categoria->nombre }} / {{ $reporte->distrito->nombre }} / Estado: {{ str_replace('_', ' ', $reporte->estado) }}</p>
        </article>

        @isset($mensaje)
            <div class="chat-box support-chat-box">
                <article class="chat-message user-message">
                    <strong>Tu consulta</strong>
                    <p>{{ $mensaje }}</p>
                </article>
                <article class="chat-message assistant-message">
                    <strong>Asistente IA</strong>
                    <p>{{ $respuesta }}</p>
                </article>
            </div>
        @else
            <div class="chat-help">
                <strong>Preguntas sugeridas</strong>
                <ul>
                    <li>De que trata el proyecto Reporte Ciudadano?</li>
                    <li>Como puedo registrar un reporte?</li>
                    <li>Que significa el estado de este reporte?</li>
                    <li>Que evidencias debo subir?</li>
                </ul>
            </div>
        @endisset

        <form method="POST" action="{{ route('reportes.chat.ask', $reporte) }}" class="form chat-form">
            @csrf
            <label>
                Mensaje
                <textarea name="mensaje" rows="4" placeholder="Escribe tu consulta sobre el reporte o el sistema..." required>{{ old('mensaje') }}</textarea>
            </label>
            <button class="button primary submit-wide" type="submit">
                <i data-lucide="bot"></i>Enviar consulta
            </button>
        </form>
    </section>
@endsection
