<div class="chat-ia-panel" id="chatIAPanelEmbed">
    <div class="chat-ia-header">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a4 4 0 014 4c0 2-2 4-4 4s-4-2-4-4 2-4 4-4z"/><path d="M2 22v-2a6 6 0 016-6h8a6 6 0 016 6v2"/></svg>
        <h4>Asistente IA</h4>
        <span class="chat-ia-status">Conectado</span>
    </div>
    <div class="chat-ia-messages" id="chatIAMessagesEmbed">
        <div class="chat-msg chat-msg-ia">
            <p>Hola! Soy el asistente virtual de ReporteCiudadano. Puedo ayudarte a entender mejor los reportes, sugerir acciones o resolver dudas sobre el proceso.</p>
        </div>
    </div>
    <div class="chat-ia-input">
        <input type="text" class="form-input" id="chatIAInputEmbed" placeholder="Escribe tu pregunta..." data-reporte-id="<?= $reporte_id ?? 0 ?>">
        <button class="btn btn-primary btn-sm" id="chatIAEnviarEmbed">Enviar</button>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var container = document.getElementById("chatIAPanelEmbed");
    if (!container) return;
    var input = document.getElementById("chatIAInputEmbed");
    var btn = document.getElementById("chatIAEnviarEmbed");
    var msgBox = document.getElementById("chatIAMessagesEmbed");
    var reporteId = input ? input.dataset.reporteId : 0;
    if (!reporteId) return;
    function addMessage(text, who) {
        var d = document.createElement("div");
        d.className = "chat-msg chat-msg-" + who;
        d.innerHTML = "<p>" + text + "</p>";
        msgBox.appendChild(d);
        msgBox.scrollTop = msgBox.scrollHeight;
    }
    function sendMessage() {
        var text = input.value.trim();
        if (!text) return;
        addMessage(text, "usuario");
        input.value = "";
        var fd = new FormData();
        fd.append("reporte_id", reporteId);
        fd.append("mensaje", text);
        fetch("<?= SITE_URL ?>/index.php?view=api&action=chat_ia_enviar", { method: "POST", body: fd })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                addMessage(d.success && d.respuesta ? d.respuesta : "Lo siento, no pude procesar tu pregunta. Intenta reformularla.", "ia");
            })
            .catch(function() {
                addMessage("Error de conexion. Verifica tu internet e intenta de nuevo.", "ia");
            });
    }
    btn.addEventListener("click", sendMessage);
    input.addEventListener("keydown", function(e) {
        if (e.key === "Enter") { e.preventDefault(); sendMessage(); }
    });
    fetch("<?= SITE_URL ?>/index.php?view=api&action=chat_ia_historial&reporte_id=" + reporteId)
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success && d.historial && d.historial.length) {
                msgBox.innerHTML = "";
                d.historial.forEach(function(m) { addMessage(m.mensaje, m.remitente); });
            }
        });
});
</script>
