/* ============================================================
   ReporteCiudadano — JavaScript Principal
   ============================================================ */

(function() {
    "use strict";

    const API_URL = "index.php?view=api&action=";

    async function apiCall(action, data, method) {
        method = method || "GET";
        const url = API_URL + action;
        const opts = { method: method };
        if (method === "POST") {
            opts.body = data instanceof FormData ? data : new URLSearchParams(data);
        }
        try {
            const res = await fetch(url, opts);
            return await res.json();
        } catch(e) {
            return { success: false, error: "Error de conexion: " + e.message };
        }
    }

    function mostrarError(id, msg) {
        const el = document.getElementById(id);
        if (el) { el.textContent = msg; el.style.display = "block"; }
    }
    function ocultarError(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = "none";
    }

    function toggleDropdown(btnId, menuId) {
        const btn = document.getElementById(btnId);
        const menu = document.getElementById(menuId);
        if (!btn || !menu) return;
        btn.addEventListener("click", function(e) {
            e.stopPropagation();
            menu.classList.toggle("open");
        });
        document.addEventListener("click", function(e) {
            if (!btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove("open");
            }
        });
    }

    function tiempoRelativo(fechaStr) {
        const ahora = new Date();
        const fecha = new Date(fechaStr.replace(" ", "T") + "Z");
        const diffMs = ahora - fecha;
        const diffMin = Math.floor(diffMs / 60000);
        const diffHoras = Math.floor(diffMs / 3600000);
        const diffDias = Math.floor(diffMs / 86400000);
        if (diffMin < 1) return "ahora";
        if (diffMin < 60) return "hace " + diffMin + " min";
        if (diffHoras < 24) return "hace " + diffHoras + " h";
        if (diffDias < 7) return "hace " + diffDias + " d";
        return fecha.toLocaleDateString("es-PE", { day: "numeric", month: "short" });
    }

    function resumirTexto(texto, max) {
        max = max || 120;
        if (texto.length <= max) return texto;
        return texto.substring(0, max).trim() + "...";
    }
    function initTheme() {
        const toggle = document.getElementById("themeToggle");
        if (!toggle) return;
        const saved = localStorage.getItem("rc_theme") || "dark";
        document.documentElement.setAttribute("data-theme", saved);
        toggle.querySelectorAll("svg").forEach(function(s) { s.style.display = "none"; });
        if (saved === "dark") {
            toggle.querySelector(".icon-moon").style.display = "block";
        } else {
            toggle.querySelector(".icon-sun").style.display = "block";
        }
        toggle.addEventListener("click", function() {
            const current = document.documentElement.getAttribute("data-theme");
            const next = current === "dark" ? "light" : "dark";
            document.documentElement.setAttribute("data-theme", next);
            localStorage.setItem("rc_theme", next);
            toggle.querySelectorAll("svg").forEach(function(s) { s.style.display = "none"; });
            if (next === "dark") {
                toggle.querySelector(".icon-moon").style.display = "block";
            } else {
                toggle.querySelector(".icon-sun").style.display = "block";
            }
        });
    }

    function initAuth() {
        const loginForm = document.getElementById("loginForm");
        if (loginForm) {
            loginForm.addEventListener("submit", async function(e) {
                e.preventDefault();
                ocultarError("loginError");
                const btn = this.querySelector('button[type="submit"]');
                btn.disabled = true; btn.textContent = "Ingresando...";
                const data = { correo: this.correo.value, password: this.password.value };
                const res = await apiCall("auth_login", data, "POST");
                if (res.success) {
                    const dashboards = {
                        ciudadano: "ciudadano_dashboard",
                        admin_municipal: "admin_dashboard",
                        operador: "operador_dashboard",
                        admin_sistema: "sistema_dashboard"
                    };
                    window.location.href = "index.php?view=" + (dashboards[res.rol] || "feed");
                } else {
                    mostrarError("loginError", res.error || "Error al iniciar sesion");
                    btn.disabled = false; btn.textContent = "Ingresar";
                }
            });
        }
        const registerForm = document.getElementById("registerForm");
        if (registerForm) {
            registerForm.addEventListener("submit", async function(e) {
                e.preventDefault();
                ocultarError("registerError");
                if (this.password.value.length < 6) {
                    mostrarError("registerError", "La contrasena debe tener al menos 6 caracteres");
                    return;
                }
                const btn = this.querySelector('button[type="submit"]');
                btn.disabled = true; btn.textContent = "Creando cuenta...";
                const data = { nombre: this.nombre.value, correo: this.correo.value, password: this.password.value, distrito_id: this.distrito_id.value };
                const res = await apiCall("auth_register", data, "POST");
                if (res.success) {
                    window.location.href = "index.php?view=ciudadano_dashboard";
                } else {
                    mostrarError("registerError", res.error || "Error al registrarse");
                    btn.disabled = false; btn.textContent = "Crear Cuenta";
                }
            });
        }
    }

    function initFeed() {
        const feedGrid = document.getElementById("feedGrid");
        const feedLoading = document.getElementById("feedLoading");
        if (!feedGrid) return;

        async function cargarReportes() {
            if (feedLoading) feedLoading.style.display = "block";
            const params = new URLSearchParams();
            const activo = document.querySelector(".chip-active");
            if (activo && activo.dataset.filter && activo.dataset.value) {
                params.set(activo.dataset.filter, activo.dataset.value);
            }
            const distrito = document.getElementById("filtroDistrito");
            if (distrito && distrito.value) params.set("distrito_id", distrito.value);
            const estado = document.getElementById("filtroEstado");
            if (estado && estado.value) params.set("estado", estado.value);
            const orden = document.getElementById("filtroOrden");
            if (orden && orden.value) params.set("orden", orden.value);
            const res = await apiCall("reportes_listar?" + params.toString());
            if (feedLoading) feedLoading.style.display = "none";
            if (res.success && res.reportes) renderReportes(res.reportes);
        }

        function renderReportes(reportes) {
            feedGrid.innerHTML = "";
            if (reportes.length === 0) {
                feedGrid.innerHTML = '<div class="text-center mt-3 mb-3"><p style="color:var(--text-muted);">No se encontraron reportes con estos filtros.</p></div>';
                return;
            }
            reportes.forEach(function(r) {
                const card = document.createElement("div");
                card.className = "card";
                const imgHtml = r.imagen_thumbnail
                    ? '<img class="card-image" src="' + r.imagen_thumbnail + '" alt="' + r.titulo + '" loading="lazy">'
                    : '<div class="card-image-placeholder"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg></div>';
                const urgenteHtml = r.es_urgente ? '<span class="badge-urgente">Urgente</span>' : "";
                card.innerHTML = imgHtml + '<div class="card-body"><div class="card-meta"><span class="card-category">' + r.categoria_nombre + '</span><span class="card-distrito">' + r.distrito_nombre + '</span>' + urgenteHtml + '</div><h3 class="card-title"><a href="index.php?view=reporte/detalle&id=' + r.id + '">' + r.titulo + '</a></h3><p class="card-desc">' + resumirTexto(r.descripcion, 120) + '</p><div class="card-footer"><div class="card-stats"><span class="card-stat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3H14zM7 22H4a2 2 0 01-2-2v-7a2 2 0 012-2h3"/></svg> ' + r.total_likes + '</span><span class="card-stat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg> ' + r.total_comentarios + '</span><span class="card-stat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/></svg> ' + (r.total_compartidos || 0) + '</span></div><span class="card-date">' + tiempoRelativo(r.fecha_creacion) + '</span></div></div>';
                feedGrid.appendChild(card);
            });
        }

        document.querySelectorAll(".chip").forEach(function(chip) {
            chip.addEventListener("click", function() {
                if (this.dataset.clear) {
                    document.querySelectorAll(".chip-active").forEach(function(c) { c.classList.remove("chip-active"); });
                    cargarReportes();
                    return;
                }
                document.querySelectorAll('.chip[data-filter="' + this.dataset.filter + '"]').forEach(function(c) { c.classList.remove("chip-active"); });
                this.classList.add("chip-active");
                cargarReportes();
            });
        });

        ["filtroDistrito", "filtroEstado", "filtroOrden"].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) el.addEventListener("change", cargarReportes);
        });

        cargarReportes();
    }
    function initInteracciones() {
        document.addEventListener("click", async function(e) {
            const btn = e.target.closest(".btn-like, .card-action-btn[data-action='like']");
            if (btn) {
                e.preventDefault();
                const reporteId = btn.dataset.reporteId || (btn.closest("[data-reporte-id]") ? btn.closest("[data-reporte-id]").dataset.reporteId : null);
                if (!reporteId) return;
                const res = await apiCall("likes_toggle", { reporte_id: reporteId }, "POST");
                if (res.success) {
                    btn.classList.toggle("liked", res.action === "liked");
                    const counter = btn.querySelector(".like-count") || (btn.parentElement ? btn.parentElement.querySelector(".like-count") : null);
                    if (counter && res.total !== undefined) counter.textContent = res.total;
                }
                return;
            }
            const saveBtn = e.target.closest(".btn-save, .card-action-btn[data-action='save']");
            if (saveBtn) {
                e.preventDefault();
                const reporteId = saveBtn.dataset.reporteId || (saveBtn.closest("[data-reporte-id]") ? saveBtn.closest("[data-reporte-id]").dataset.reporteId : null);
                if (!reporteId) return;
                const res = await apiCall("guardados_toggle", { reporte_id: reporteId }, "POST");
                if (res.success) saveBtn.classList.toggle("saved", res.action === "saved");
                return;
            }
            const shareBtn = e.target.closest(".btn-share, .card-action-btn[data-action='share']");
            if (shareBtn) {
                e.preventDefault();
                const reporteId = shareBtn.dataset.reporteId || (shareBtn.closest("[data-reporte-id]") ? shareBtn.closest("[data-reporte-id]").dataset.reporteId : null);
                if (!reporteId) return;
                const url = window.location.origin + "/" + "index.php?view=reporte/detalle&id=" + reporteId;
                if (navigator.share) { navigator.share({ url: url }); }
                else if (navigator.clipboard) { navigator.clipboard.writeText(url).then(function() { alert("Enlace copiado al portapapeles"); }); }
                apiCall("compartidos_crear", { reporte_id: reporteId }, "POST");
            }
        });
    }

    function initComentarios() {
        const form = document.getElementById("comentarioForm");
        if (!form) return;
        form.addEventListener("submit", async function(e) {
            e.preventDefault();
            const input = this.querySelector("textarea, input[type='text']");
            if (!input || !input.value.trim()) return;
            const reporteId = this.dataset.reporteId;
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            const res = await apiCall("comentarios_crear", { reporte_id: reporteId, contenido: input.value.trim() }, "POST");
            if (res.success) { input.value = ""; cargarComentarios(reporteId); }
            else { alert(res.error || "Error al enviar comentario"); }
            btn.disabled = false;
        });
    }

    async function cargarComentarios(reporteId) {
        const lista = document.getElementById("comentariosLista");
        if (!lista) return;
        const res = await apiCall("comentarios_listar?reporte_id=" + reporteId);
        if (res.success && res.comentarios) {
            lista.innerHTML = "";
            if (res.comentarios.length === 0) {
                lista.innerHTML = '<p style="color:var(--text-muted);font-size:0.875rem;">No hay comentarios aun. Se el primero en comentar.</p>';
                return;
            }
            res.comentarios.forEach(function(c) {
                const div = document.createElement("div");
                div.style.cssText = "padding:0.75rem 0;border-bottom:1px solid var(--border-color);";
                div.innerHTML = "<strong style='font-size:0.8125rem;'>" + c.usuario_nombre + "</strong> <span style='font-size:0.75rem;color:var(--text-muted);'>" + tiempoRelativo(c.fecha) + "</span><p style='font-size:0.875rem;margin:0.25rem 0 0;'>" + c.contenido + "</p>";
                lista.appendChild(div);
            });
        }
    }

    function initNotificaciones() {
        const notifBtn = document.getElementById("notifBtn");
        const notifDropdown = document.getElementById("notifDropdown");
        const notifList = document.getElementById("notifList");
        const notifCount = document.getElementById("notifCount");
        if (!notifBtn) return;

        async function cargar() {
            const res = await apiCall("notificaciones_listar");
            if (!res.success) return;
            if (notifCount) {
                if (res.contar > 0) { notifCount.textContent = res.contar; notifCount.style.display = "flex"; }
                else { notifCount.style.display = "none"; }
            }
            if (notifList) {
                notifList.innerHTML = "";
                if (res.no_leidas.length === 0 && res.todas.length === 0) {
                    notifList.innerHTML = '<p class="notif-empty">No tienes notificaciones</p>';
                    return;
                }
                const items = res.no_leidas.length > 0 ? res.no_leidas : res.todas.slice(0, 10);
                items.forEach(function(n) {
                    const div = document.createElement("div");
                    div.className = "notif-item" + (n.leida ? "" : " no-leida");
                    div.innerHTML = "<p>" + n.mensaje + "</p><small>" + tiempoRelativo(n.fecha) + "</small>";
                    div.addEventListener("click", function() { apiCall("notificaciones_marcar", { id: n.id }, "POST"); div.classList.remove("no-leida"); });
                    notifList.appendChild(div);
                });
            }
        }

        notifBtn.addEventListener("click", function(e) {
            e.stopPropagation();
            notifDropdown.classList.toggle("open");
            if (notifDropdown.classList.contains("open")) cargar();
        });
        document.addEventListener("click", function(e) {
            if (!notifBtn.contains(e.target) && !notifDropdown.contains(e.target)) notifDropdown.classList.remove("open");
        });
        const marcarBtn = document.getElementById("marcarLeidas");
        if (marcarBtn) {
            marcarBtn.addEventListener("click", async function() {
                await apiCall("notificaciones_marcar", { id: 0 }, "POST");
                if (notifCount) notifCount.style.display = "none";
                document.querySelectorAll(".notif-item.no-leida").forEach(function(el) { el.classList.remove("no-leida"); });
            });
        }
        cargar();
        setInterval(cargar, 30000);
    }
    function initMapa() {
        const container = document.getElementById("mapaReportes");
        if (!container) return;
        const mapa = L.map("mapaReportes", {
            center: [-16.4090, -71.5375],
            zoom: 13, maxBounds: [[-16.55, -71.65], [-16.25, -71.35]], maxBoundsViscosity: 1.0
        });
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>', maxZoom: 18
        }).addTo(mapa);
        const iconos = {
            "default": L.divIcon({ html: '<div style="background:#6E6E6E;color:white;width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:12px;border:2px solid white;">R</div>', className: "", iconSize: [24, 24] })
        };
        async function cargar() {
            const res = await apiCall("mapa_reportes");
            if (!res.success || !res.reportes) return;
            res.reportes.forEach(function(r) {
                if (!r.latitud || !r.longitud) return;
                const icon = iconos[r.categoria_nombre] || iconos["default"];
                const marker = L.marker([parseFloat(r.latitud), parseFloat(r.longitud)], { icon: icon }).addTo(mapa);
                marker.bindPopup('<div style="font-size:0.75rem;text-align:center;"><strong>' + r.titulo + '</strong><br>' + r.distrito_nombre + '<br><span style="color:#6E6E6E;">' + r.categoria_nombre + '</span></div>');
            });
        }
        cargar();
        return mapa;
    }

    function initMapaCrear() {
        const container = document.getElementById("mapaCrear");
        if (!container) return;
        const mapa = L.map("mapaCrear", {
            center: [-16.4090, -71.5375],
            zoom: 14, maxBounds: [[-16.55, -71.65], [-16.25, -71.35]], maxBoundsViscosity: 1.0
        });
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "&copy; OpenStreetMap", maxZoom: 18
        }).addTo(mapa);
        let marker = null;
        mapa.on("click", function(e) {
            if (marker) mapa.removeLayer(marker);
            marker = L.marker(e.latlng).addTo(mapa);
            document.getElementById("latitud").value = e.latlng.lat.toFixed(7);
            document.getElementById("longitud").value = e.latlng.lng.toFixed(7);
        });
    }

    function initClima() {
        const widget = document.getElementById("climaWidget");
        if (!widget) return;
        const distritoId = widget.dataset.distritoId || "";
        async function cargar() {
            const params = distritoId ? "?distrito_id=" + distritoId : "";
            const res = await apiCall("clima_obtener" + params);
            if (res.success && res.clima) {
                widget.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg> <span class="clima-temp">' + res.clima.temperatura + '°C</span> <span class="clima-desc">' + res.clima.descripcion + " &middot; Humedad: " + res.clima.humedad + "%</span>";
                if (res.clima.nota && res.clima.simulado) widget.title = res.clima.nota;
            }
        }
        cargar();
    }
    function initChatIA() {
        const chatPanel = document.getElementById("chatPanel");
        const chatBtn = document.getElementById("chatIaBtn");
        const chatClose = document.getElementById("chatClose");
        const chatInput = document.getElementById("chatInput");
        const chatSend = document.getElementById("chatSend");
        const chatMensajes = document.getElementById("chatMensajes");
        if (!chatPanel) return;
        let reporteId = chatPanel.dataset.reporteId;
        let conversacionId = null;

        if (chatBtn) {
            chatBtn.addEventListener("click", function() {
                chatPanel.classList.toggle("open");
                if (chatPanel.classList.contains("open") && chatMensajes) cargarHistorial();
            });
        }
        if (chatClose) {
            chatClose.addEventListener("click", function() { chatPanel.classList.remove("open"); });
        }

        async function cargarHistorial() {
            const res = await apiCall("chat_ia_historial?reporte_id=" + reporteId);
            if (res.success && res.historial) {
                conversacionId = res.conversacion_id;
                chatMensajes.innerHTML = "";
                if (res.historial.length === 0) {
                    chatMensajes.innerHTML = '<p style="color:var(--text-muted);font-size:0.8125rem;text-align:center;">Inicia una conversacion sobre este reporte</p>';
                    return;
                }
                res.historial.forEach(function(msg) { agregarMensaje(msg.mensaje, msg.remitente === "ia" ? "ia" : "usuario"); });
                chatMensajes.scrollTop = chatMensajes.scrollHeight;
            }
        }

        function agregarMensaje(texto, tipo) {
            const div = document.createElement("div");
            div.className = "chat-mensaje chat-mensaje-" + tipo;
            div.textContent = texto;
            chatMensajes.appendChild(div);
            chatMensajes.scrollTop = chatMensajes.scrollHeight;
        }

        async function enviarMensaje() {
            const texto = chatInput.value.trim();
            if (!texto) return;
            chatInput.value = "";
            agregarMensaje(texto, "usuario");
            chatSend.disabled = true; chatSend.textContent = "...";
            const res = await apiCall("chat_ia_enviar", { reporte_id: reporteId, mensaje: texto }, "POST");
            if (res.success) {
                conversacionId = res.conversacion_id;
                agregarMensaje(res.respuesta, "ia");
            } else {
                agregarMensaje("Error al obtener respuesta. Intenta de nuevo.", "ia");
            }
            chatSend.disabled = false; chatSend.textContent = "Enviar";
        }

        if (chatSend) chatSend.addEventListener("click", enviarMensaje);
        if (chatInput) {
            chatInput.addEventListener("keydown", function(e) {
                if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); enviarMensaje(); }
            });
        }
    }

    function initAdmin() {
        const modal = document.getElementById("asignarModal");
        if (!modal) return;
        document.querySelectorAll(".btn-asignar").forEach(function(btn) {
            btn.addEventListener("click", function() {
                document.getElementById("asignarReporteId").value = this.dataset.reporteId;
                modal.classList.add("open");
            });
        });
        document.querySelectorAll(".modal-close, .modal-cancel").forEach(function(el) {
            el.addEventListener("click", function() { modal.classList.remove("open"); });
        });
        const form = document.getElementById("asignarForm");
        if (form) {
            form.addEventListener("submit", async function(e) {
                e.preventDefault();
                const res = await apiCall("admin_asignar", { reporte_id: this.reporte_id.value, operador_id: this.operador_id.value }, "POST");
                if (res.success) { modal.classList.remove("open"); location.reload(); }
                else { alert(res.error || "Error al asignar"); }
            });
        }
    }

    function initOperador() {
        document.querySelectorAll(".btn-cambiar-estado").forEach(function(btn) {
            btn.addEventListener("click", async function() {
                const reporteId = this.dataset.reporteId;
                const estado = this.dataset.estado;
                if (!confirm('Cambiar estado a "' + estado.replace(/_/g, " ") + '"?')) return;
                const res = await apiCall("operador_cambiar_estado", { reporte_id: reporteId, estado: estado }, "POST");
                if (res.success) location.reload();
                else alert(res.error || "Error al cambiar estado");
            });
        });
    }

    function initSistema() {
        document.querySelectorAll(".btn-suspender, .btn-activar, .btn-eliminar").forEach(function(btn) {
            btn.addEventListener("click", async function() {
                if (!confirm("Estas seguro de " + this.dataset.accion + " este usuario?")) return;
                const res = await apiCall("sistema_usuario_estado", { id: this.dataset.userId, accion: this.dataset.accion }, "POST");
                if (res.success) location.reload();
                else alert(res.error || "Error");
            });
        });
        const editForm = document.getElementById("editarUsuarioForm");
        if (editForm) {
            editForm.addEventListener("submit", async function(e) {
                e.preventDefault();
                const data = { id: this.user_id.value, nombre: this.nombre.value, correo: this.correo.value, rol: this.rol.value, distrito_id: this.distrito_id.value };
                if (this.password.value) data.password = this.password.value;
                const res = await apiCall("sistema_usuario_actualizar", data, "POST");
                if (res.success) {
                    document.querySelectorAll(".modal-overlay.open").forEach(function(m) { m.classList.remove("open"); });
                    location.reload();
                } else { alert(res.error || "Error al actualizar"); }
            });
        }
    }

    function initModales() {
        document.querySelectorAll(".modal-overlay").forEach(function(modal) {
            modal.addEventListener("click", function(e) { if (e.target === this) this.classList.remove("open"); });
        });
        document.querySelectorAll(".modal-close").forEach(function(btn) {
            btn.addEventListener("click", function() { this.closest(".modal-overlay").classList.remove("open"); });
        });
    }

    function initFileUpload() {
        document.querySelectorAll('.file-upload input[type="file"]').forEach(function(input) {
            input.addEventListener("change", function() {
                const text = this.parentElement.querySelector(".file-upload-text");
                if (text && this.files.length > 0) {
                    text.textContent = this.files.length + " archivo(s) seleccionado(s)";
                }
            });
        });
    }

    function initScrollTop() {
        const btn = document.getElementById("scrollTop");
        if (!btn) return;
        window.addEventListener("scroll", function() { btn.style.display = window.scrollY > 300 ? "flex" : "none"; });
        btn.addEventListener("click", function() { window.scrollTo({ top: 0, behavior: "smooth" }); });
    }

    document.addEventListener("DOMContentLoaded", function() {
        initTheme(); initAuth(); initFeed(); initInteracciones(); initComentarios();
        initNotificaciones(); initMapa(); initMapaCrear(); initClima(); initChatIA();
        initAdmin(); initOperador(); initSistema(); initModales(); initFileUpload();
        initScrollTop();
        toggleDropdown("userMenuBtn", "userDropdown");
    });

})();
