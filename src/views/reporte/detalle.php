<?php
$reporte_id = (int)($_GET["id"] ?? 0);
$reporte = Reporte::porId($reporte_id);
if (!$reporte):
?>
<main class="main-content">
    <div class="container">
        <div class="error-page">
            <h1>Reporte no encontrado</h1>
            <p>El reporte que buscas no existe o ha sido eliminado.</p>
            <a href="<?= SITE_URL ?>/index.php?view=feed" class="btn btn-primary">Volver al inicio</a>
        </div>
    </div>
</main>
<?php return; endif;
$imagenes = Reporte::imagenes($reporte_id);
$videos = Reporte::videos($reporte_id);
$latitud = $reporte["latitud"] ?? -16.4090;
$longitud = $reporte["longitud"] ?? -71.5375;
$iconoDetalle = trim((string)($reporte["categoria_icono"] ?? ""));
$mostrarIconoDetalle = $iconoDetalle !== "" && !preg_match('/^[a-zA-Z0-9_ -]+$/u', $iconoDetalle);
?>
<section class="detalle-header">
    <div class="container">
        <a href="<?= SITE_URL ?>/index.php?view=feed" class="btn-back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver al feed
        </a>
    </div>
</section>
<section class="detalle-content">
    <div class="container detalle-layout">
        <div class="detalle-main">
            <?php if (!empty($imagenes)): ?>
            <div class="detalle-gallery">
                <div class="gallery-main">
                    <img src="<?= SITE_URL ?>/<?= htmlspecialchars($imagenes[0]["ruta_archivo"]) ?>" alt="<?= htmlspecialchars($reporte["titulo"]) ?>" id="galleryMain">
                </div>
                <?php if (count($imagenes) > 1): ?>
                <div class="gallery-thumbs">
                    <?php foreach ($imagenes as $i => $img): ?>
                    <button class="gallery-thumb <?= $i === 0 ? "active" : "" ?>" data-src="<?= SITE_URL ?>/<?= htmlspecialchars($img["ruta_archivo"]) ?>">
                        <img src="<?= SITE_URL ?>/<?= htmlspecialchars($img["ruta_archivo"]) ?>" alt="">
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($videos)): foreach ($videos as $v): ?>
            <div class="detalle-video">
                <video controls class="detalle-video-player">
                    <source src="<?= SITE_URL ?>/<?= htmlspecialchars($v["ruta_archivo"]) ?>" type="video/mp4">
                </video>
            </div>
            <?php endforeach; endif; ?>
            <div class="detalle-meta">
                <div class="detalle-badges">
                    <span class="chip chip-categoria">
                        <?php if ($mostrarIconoDetalle): ?><span class="chip-icon"><?= htmlspecialchars($iconoDetalle) ?></span><?php endif; ?>
                        <?= htmlspecialchars($reporte["categoria_nombre"]) ?>
                    </span>
                    <span class="chip"><?= htmlspecialchars($reporte["distrito_nombre"]) ?></span>
                    <?php if (!empty($reporte["es_urgente"])): ?>
                    <span class="chip chip-urgente">Urgente</span>
                    <?php endif; ?>
                    <span class="status-badge status-<?= htmlspecialchars($reporte["estado"]) ?>"><?= htmlspecialchars(ucfirst(str_replace("_", " ", $reporte["estado"]))) ?></span>
                </div>
                <span class="detalle-date">Publicado <?= tiempoRelativo($reporte["fecha_creacion"]) ?> por <strong><?= htmlspecialchars($reporte["usuario_nombre"]) ?></strong></span>
            </div>
            <h1 class="detalle-title"><?= htmlspecialchars($reporte["titulo"]) ?></h1>
            <div class="detalle-descripcion"><?= nl2br(htmlspecialchars($reporte["descripcion"])) ?></div>
            <div class="detalle-acciones">
                <button class="btn btn-like <?= !empty($reporte["tiene_like"]) ? "active" : "" ?>" data-action="like" data-reporte-id="<?= $reporte_id ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="<?= !empty($reporte["tiene_like"]) ? "currentColor" : "none" ?>" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                    <span id="likeCount"><?= (int)$reporte["total_likes"] ?></span> Me gusta
                </button>
                <button class="btn btn-save <?= !empty($reporte["tiene_guardado"]) ? "active" : "" ?>" data-action="save" data-reporte-id="<?= $reporte_id ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="<?= !empty($reporte["tiene_guardado"]) ? "currentColor" : "none" ?>" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
                    Guardar
                </button>
                <button class="btn btn-share" data-action="share" data-reporte-id="<?= $reporte_id ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/></svg>
                    Compartir
                </button>
            </div>
            <div class="detalle-mapa" id="detalleMapa">
                <h3>Ubicación</h3>
                <?php $draggable = false; require dirname(__DIR__) . '/partials/mapa.php'; ?>
            </div>
            <div class="detalle-comentarios" id="comentarios">
                <h3>Comentarios <span id="comentariosCount">(<?= (int)$reporte["total_comentarios"] ?>)</span></h3>
                <div class="comentarios-form">
                    <textarea id="comentarioInput" class="form-input form-textarea" rows="3" placeholder="Escribe un comentario..."></textarea>
                    <button class="btn btn-primary" id="comentarioEnviar" data-reporte-id="<?= $reporte_id ?>">Enviar</button>
                </div>
                <div class="comentarios-list" id="comentariosList"><p class="comentarios-loading">Cargando comentarios...</p></div>
            </div>
        </div>
        <aside class="detalle-sidebar">
            <?php require_once dirname(__DIR__) . '/partials/clima-widget.php'; ?>
            <div class="chat-ia-panel" id="chatIAPanel">
                <div class="chat-ia-header">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a4 4 0 014 4c0 2-2 4-4 4s-4-2-4-4 2-4 4-4z"/><path d="M2 22v-2a6 6 0 016-6h8a6 6 0 016 6v2"/></svg>
                    <h4>Asistente IA</h4>
                </div>
                <div class="chat-ia-messages" id="chatIAMessages">
                    <div class="chat-ia-welcome"><p>Hola! Soy el asistente de ReporteCiudadano. Preguntame sobre este reporte.</p></div>
                </div>
                <div class="chat-ia-input">
                    <input type="text" id="chatIAInput" class="form-input" placeholder="Escribe tu pregunta..." data-reporte-id="<?= $reporte_id ?>">
                    <button class="btn btn-primary btn-sm" id="chatIAEnviar">Enviar</button>
                </div>
            </div>
        </aside>
    </div>
</section>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var thumbs = document.querySelectorAll(".gallery-thumb");
    thumbs.forEach(function(t) {
        t.addEventListener("click", function() {
            document.querySelectorAll(".gallery-thumb").forEach(function(x) { x.classList.remove("active"); });
            this.classList.add("active");
            document.getElementById("galleryMain").src = this.dataset.src;
        });
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {

var cList = document.getElementById("comentariosList");
var cBtn = document.getElementById("comentarioEnviar");
var cInput = document.getElementById("comentarioInput");
var rid = cBtn.dataset.reporteId;

function loadComments() {
    fetch("<?= SITE_URL ?>/index.php?view=api&action=comentarios_listar&reporte_id=" + rid)
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success && d.comentarios) {
                if (!d.comentarios.length) {
                    cList.innerHTML = "<p class=\\\"comentarios-empty\\\">No hay comentarios aun. Se el primero en comentar.</p>";
                } else {
                    var html = "";
                    d.comentarios.forEach(function(c) {
                        html += "<div class=\\\"comentario-item\\\"><div class=\\\"comentario-avatar\\\">" + (c.usuario_nombre ? c.usuario_nombre.charAt(0).toUpperCase() : "?") + "</div><div class=\\\"comentario-body\\\"><strong>" + (c.usuario_nombre || "Usuario") + "</strong><p>" + c.contenido + "</p><span class=\\\"comentario-date\\\">" + c.fecha + "</span></div></div>";
                    });
                    cList.innerHTML = html;
                }
            }
        });
    }
    loadComments();

    cBtn.addEventListener("click", function() {
        var txt = cInput.value.trim();
        if (!txt) return;
        var fd = new FormData();
        fd.append("reporte_id", rid);
        fd.append("contenido", txt);
        fetch("<?= SITE_URL ?>/index.php?view=api&action=comentarios_crear", { method: "POST", body: fd })

            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) { cInput.value = ""; loadComments(); }
            });
    });
    cInput.addEventListener("keydown", function(e) {
        if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); cBtn.click(); }
    });

    var chatIn = document.getElementById("chatIAInput");
    var chatBtn = document.getElementById("chatIAEnviar");
    var chatMsg = document.getElementById("chatIAMessages");
    function addMsg(txt, who) {
        var d = document.createElement("div");
        d.className = "chat-msg chat-msg-" + who;
        d.innerHTML = "<p>" + txt + "</p>";
        chatMsg.appendChild(d);
        chatMsg.scrollTop = chatMsg.scrollHeight;
    }

    chatIn.addEventListener("keydown", function(e) {

        if (e.key === "Enter") { e.preventDefault(); chatBtn.click(); }
    });

    fetch("<?= SITE_URL ?>/index.php?view=api&action=chat_ia_historial&reporte_id=" + rid)

        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success && d.historial && d.historial.length) {

                chatMsg.innerHTML = "";
                d.historial.forEach(function(m) { addMsg(m.mensaje, m.remitente); });
            }
        });

    chatBtn.addEventListener("click", function() {
        var msg = chatIn.value.trim();
        if (!msg) return;
        addMsg(msg, "usuario");
        chatIn.value = "";

        var fd = new FormData();
        fd.append("reporte_id", rid);
        fd.append("mensaje", msg);
        fetch("<?= SITE_URL ?>/index.php?view=api&action=chat_ia_enviar", { method: "POST", body: fd })

            .then(function(r) { return r.json(); })
            .then(function(d) {
                addMsg(d.success && d.respuesta ? d.respuesta : "Lo siento, no pude procesar tu pregunta.", "ia");
            })
            .catch(function() { addMsg("Error de conexion. Intenta de nuevo.", "ia"); });
    });

});
</script>
