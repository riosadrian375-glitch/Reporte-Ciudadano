@extends('layouts.app')

@section('title', $reporte->titulo . ' - Reporte Ciudadano')

@section('content')
    <section class="page report-detail-page">
        <article class="detail-shell">
            <header class="detail-hero">
                <div>
                    <p class="kicker">{{ $reporte->categoria->nombre }} / {{ $reporte->distrito->nombre }}</p>
                    <h1>{{ $reporte->titulo }}</h1>
                    <p class="detail-summary">{{ $reporte->descripcion }}</p>
                </div>

                <div class="detail-status">
                    <span class="status-pill">{{ $reporte->estado }}</span>
                    <span class="status-pill muted">{{ $reporte->estado_moderacion }}</span>
                    @if ($reporte->es_urgente)
                        <span class="status-pill urgent">Urgente</span>
                    @endif
                </div>
            </header>

            <div class="detail-grid">
                <section class="detail-main-card">
                    <div class="detail-facts">
                        @if ($reporte->direccion)
                            <div>
                                <span>Direccion</span>
                                <strong>{{ $reporte->direccion }}</strong>
                            </div>
                        @endif
                        <div>
                            <span>Reportado por</span>
                            <strong>{{ $reporte->user->name }}</strong>
                        </div>
                        <div>
                            <span>Interacciones</span>
                            <strong>{{ $reporte->likes_count }} me gusta / {{ $reporte->guardados_count }} guardados</strong>
                        </div>
                    </div>

                    @if ($reporte->imagenes->isNotEmpty() || $reporte->videos->isNotEmpty())
                        <div class="media-grid detail-media">
                            @foreach ($reporte->imagenes as $imagen)
                                <img src="{{ asset('storage/' . $imagen->ruta_archivo) }}" alt="Evidencia del reporte">
                            @endforeach
                            @foreach ($reporte->videos as $video)
                                <video controls preload="metadata">
                                    <source src="{{ asset('storage/' . $video->ruta_archivo) }}">
                                </video>
                            @endforeach
                        </div>
                    @endif

                    @auth
                        <div class="detail-actions">
                            <form method="POST" action="{{ route('reportes.like', $reporte) }}">
                                @csrf
                                <button class="detail-action" type="submit">
                                    <i data-lucide="thumbs-up"></i>
                                    <span>Me gusta</span>
                                    <strong>{{ $reporte->likes_count }}</strong>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('reportes.guardar', $reporte) }}">
                                @csrf
                                <button class="detail-action" type="submit">
                                    <i data-lucide="bookmark"></i>
                                    <span>Guardar</span>
                                    <strong>{{ $reporte->guardados_count }}</strong>
                                </button>
                            </form>
                            <a class="detail-action" href="{{ route('reportes.chat.show', $reporte) }}">
                                <i data-lucide="bot"></i>
                                <span>Chat IA</span>
                            </a>
                            @if (auth()->user()->role !== 'ciudadano')
                                <a class="detail-action primary" href="{{ route('reportes.evidencias.create', $reporte) }}">
                                    <i data-lucide="check-circle"></i>
                                    <span>Registrar cierre</span>
                                </a>
                            @endif
                        </div>
                    @endauth
                </section>

                <aside class="detail-side-card">
                    <strong>Resumen del reporte</strong>
                    <dl>
                        <div>
                            <dt>Categoria</dt>
                            <dd>{{ $reporte->categoria->nombre }}</dd>
                        </div>
                        <div>
                            <dt>Distrito</dt>
                            <dd>{{ $reporte->distrito->nombre }}</dd>
                        </div>
                        <div>
                            <dt>Estado</dt>
                            <dd>{{ ucfirst(str_replace('_', ' ', $reporte->estado)) }}</dd>
                        </div>
                        <div>
                            <dt>Moderacion</dt>
                            <dd>{{ ucfirst(str_replace('_', ' ', $reporte->estado_moderacion)) }}</dd>
                        </div>
                    </dl>
                </aside>
            </div>
        </article>

        @if ($reporte->evidencias->isNotEmpty())
            <section class="detail-section">
                <h2>Evidencias de atencion</h2>
                <div class="comment-list">
                    @foreach ($reporte->evidencias as $evidencia)
                        <article class="comment detail-comment">
                            <div class="comment-head">
                                <strong>{{ $evidencia->operador->name }}</strong>
                                <a href="{{ asset('storage/' . $evidencia->ruta_archivo) }}" target="_blank" rel="noreferrer">Ver archivo</a>
                            </div>
                            <p>{{ $evidencia->comentario_resolucion }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="detail-section comments-panel">
            <div class="section-title-row">
                <div>
                    <p class="kicker">Participacion</p>
                    <h2>Comentarios</h2>
                </div>
                <span>{{ $reporte->comentarios_count }} comentario(s)</span>
            </div>

            @auth
                <form method="POST" action="{{ route('reportes.comentarios.store', $reporte) }}" class="comment-form">
                    @csrf
                    <label for="contenido">Nuevo comentario</label>
                    <textarea id="contenido" name="contenido" rows="3" placeholder="Escribe una consulta o aporte sobre este reporte..." required>{{ old('contenido') }}</textarea>
                    @error('contenido') <span class="error">{{ $message }}</span> @enderror
                    <button class="button primary submit-wide" type="submit">
                        <i data-lucide="message-circle"></i>Comentar
                    </button>
                </form>
            @endauth

            <div class="comment-list refined-comments">
                @forelse ($reporte->comentarios as $comentario)
                    <article class="comment detail-comment">
                        <div class="comment-head">
                            <strong>{{ $comentario->user->name }}</strong>
                            <small>{{ $comentario->created_at->diffForHumans() }}</small>
                        </div>
                        <p>{{ $comentario->contenido }}</p>
                    </article>
                @empty
                    <p class="empty-state">No hay comentarios todavia.</p>
                @endforelse
            </div>
        </section>
    </section>
@endsection
