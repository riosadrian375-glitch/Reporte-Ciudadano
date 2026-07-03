@php
    $likesCount = $reporte->likes_count ?? $reporte->likes()->count();
    $comentariosCount = $reporte->comentarios_count ?? $reporte->comentarios()->count();
    $guardadosCount = $reporte->guardados_count ?? $reporte->guardados()->count();
@endphp

<article class="report-card">
    <div class="card-top">
        <span>{{ $reporte->categoria->nombre }}</span>
        @if ($reporte->es_urgente)
            <strong>Urgente</strong>
        @endif
    </div>

    <h3><a href="{{ route('reportes.show', $reporte) }}">{{ $reporte->titulo }}</a></h3>
    <p>{{ Str::limit($reporte->descripcion, 130) }}</p>

    <div class="meta">
        <span>{{ $reporte->distrito->nombre }}</span>
        <span>{{ $reporte->estado }}</span>
    </div>

    <div class="card-actions">
        @auth
            <form method="POST" action="{{ route('reportes.like', $reporte) }}">
                @csrf
                <button class="card-action-button" type="submit" title="Me gusta" aria-label="Me gusta">
                    <i data-lucide="thumbs-up"></i><span>{{ $likesCount }}</span>
                </button>
            </form>
            <form method="POST" action="{{ route('reportes.guardar', $reporte) }}">
                @csrf
                <button class="card-action-button" type="submit" title="Guardar" aria-label="Guardar">
                    <i data-lucide="bookmark"></i><span>{{ $guardadosCount }}</span>
                </button>
            </form>
        @else
            <a class="card-action-link" href="{{ route('login') }}" title="Me gusta" aria-label="Me gusta">
                <i data-lucide="thumbs-up"></i><span>{{ $likesCount }}</span>
            </a>
            <a class="card-action-link" href="{{ route('login') }}" title="Guardar" aria-label="Guardar">
                <i data-lucide="bookmark"></i><span>{{ $guardadosCount }}</span>
            </a>
        @endauth
        <a class="card-action-link" href="{{ route('reportes.show', $reporte) }}" title="Ver detalle" aria-label="Ver detalle">
            <i data-lucide="eye"></i>
        </a>
    </div>

    <div class="card-stats">
        <span><i data-lucide="message-circle"></i>{{ $comentariosCount }} comentarios</span>
    </div>

    @auth
        <form method="POST" action="{{ route('reportes.comentarios.store', $reporte) }}" class="quick-comment">
            @csrf
            <input type="text" name="contenido" placeholder="Comentar en este reporte..." required maxlength="1000">
            <button type="submit" title="Enviar comentario" aria-label="Enviar comentario">
                <i data-lucide="send"></i>
            </button>
        </form>
    @else
        <a class="quick-comment-login" href="{{ route('login') }}">Inicia sesión para comentar</a>
    @endauth
</article>
