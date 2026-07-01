-- ============================================================
-- ReporteCiudadano — Datos Semilla (Seed Data)
-- ============================================================

USE reporteciudadano;

-- ============================================================
-- DISTRITOS DE AREQUIPA
-- ============================================================
INSERT INTO distritos (nombre) VALUES
('Cercado'),
('Miraflores'),
('Cayma'),
('Cerro Colorado'),
('Yanahuara'),
('Paucarpata'),
('José Luis Bustamante y Rivero'),
('Mariano Melgar'),
('Alto Selva Alegre'),
('Sachaca'),
('Tiabaya'),
('Hunter'),
('Socabaya'),
('Characato'),
('Sabandía'),
('Quequeña'),
('Polobaya'),
('Yarabamba'),
('Chiguata'),
('Uchumayo');

-- ============================================================
-- CATEGORÍAS DE REPORTES
-- ============================================================
INSERT INTO categorias (nombre, icono) VALUES
('Huecos en vías', 'huecos'),
('Basura acumulada', 'basura'),
('Alumbrado dañado', 'alumbrado'),
('Seguridad ciudadana', 'seguridad'),
('Servicios públicos', 'servicios'),
('Clima y desastres', 'clima'),
('Salud pública', 'salud'),
('Tránsito y transporte', 'transito');

-- ============================================================
-- CONTACTOS DE EMERGENCIA
-- ============================================================
INSERT INTO emergencias_contactos (nombre_servicio, numero, descripcion) VALUES
('Policía Nacional', '105', 'Emergencias policiales, robos, incidentes de seguridad.'),
('Bomberos', '116', 'Incendios, rescates, emergencias estructurales.'),
('SAMU', '106', 'Emergencias médicas, ambulancias, atención prehospitalaria.'),
('Serenazgo', '054-201234', 'Seguridad ciudadana municipal, apoyo en vía pública.'),
('ESSALUD', '054-215000', 'Atención médica de emergencia, consultas hospitalarias.'),
('Defensa Civil', '054-204040', 'Desastres naturales, sismos, alertas climáticas.'),
('Central de Emergencias', '911', 'Emergencias generales (número nacional).');

-- ============================================================
-- USUARIOS DE PRUEBA (contraseña: "test1234" para todos)
-- Hash generado con password_hash('test1234', PASSWORD_DEFAULT)
-- ============================================================
INSERT INTO usuarios (nombre, correo, password_hash, rol, distrito_id, estado) VALUES
('Carlos Mendoza', 'carlos@example.com', '$2y$10$TUWgCRlayDv2C.XUgE1Aae76huXp/IVWv9TcvsyiCYVLCWQbVeNGa', 'ciudadano', 1, 'activo'),
('María Torres', 'maria@example.com', '$2y$10$TUWgCRlayDv2C.XUgE1Aae76huXp/IVWv9TcvsyiCYVLCWQbVeNGa', 'ciudadano', 3, 'activo'),
('Admin Municipal', 'admin@municipal.gob.pe', '$2y$10$TUWgCRlayDv2C.XUgE1Aae76huXp/IVWv9TcvsyiCYVLCWQbVeNGa', 'admin_municipal', 1, 'activo'),
('Operador Técnico 1', 'operador1@municipal.gob.pe', '$2y$10$TUWgCRlayDv2C.XUgE1Aae76huXp/IVWv9TcvsyiCYVLCWQbVeNGa', 'operador', 1, 'activo'),
('Operador Técnico 2', 'operador2@municipal.gob.pe', '$2y$10$TUWgCRlayDv2C.XUgE1Aae76huXp/IVWv9TcvsyiCYVLCWQbVeNGa', 'operador', 3, 'activo'),
('Admin Sistema', 'sysadmin@reporteciudadano.pe', '$2y$10$TUWgCRlayDv2C.XUgE1Aae76huXp/IVWv9TcvsyiCYVLCWQbVeNGa', 'admin_sistema', 1, 'activo'),
('Ana Gutiérrez', 'ana@example.com', '$2y$10$TUWgCRlayDv2C.XUgE1Aae76huXp/IVWv9TcvsyiCYVLCWQbVeNGa', 'ciudadano', 2, 'activo'),
('Pedro Huerta', 'pedro@example.com', '$2y$10$TUWgCRlayDv2C.XUgE1Aae76huXp/IVWv9TcvsyiCYVLCWQbVeNGa', 'ciudadano', 4, 'activo');

-- ============================================================
-- REPORTES DE EJEMPLO
-- ============================================================
INSERT INTO reportes (usuario_id, categoria_id, distrito_id, titulo, descripcion, latitud, longitud, estado, moderado, estado_moderacion) VALUES
(1, 1, 1, 'Hueco peligroso en Av. Ejército', 'Hay un hueco de aproximadamente 50cm de diámetro en la intersección de Av. Ejército con Calle San Juan. Muy peligroso para vehículos y peatones, especialmente de noche.', -16.4090, -71.5375, 'pendiente', TRUE, 'aprobado'),
(1, 2, 3, 'Basura acumulada en Parque Cayma', 'Se han acumulado residuos domésticos y escombros en el parque principal de Cayma desde hace más de una semana. Mal olor y presencia de roedores.', -16.3780, -71.5430, 'en_proceso', TRUE, 'aprobado'),
(2, 3, 2, 'Poste de alumbrado apagado en Miraflores', 'El poste de alumbrado público en la calle Los Olivos 123 está apagado desde hace 15 días. La cuadra queda completamente a oscuras.', -16.4110, -71.5280, 'pendiente', TRUE, 'aprobado'),
(7, 4, 1, 'Robos frecuentes en Av. La Marina', 'Vecinos reportan robos al paso frecuentes entre las 7pm y 10pm en la Av. La Marina, cerca del puente. Se necesita mayor patrullaje.', -16.4050, -71.5400, 'pendiente', TRUE, 'aprobado'),
(8, 5, 4, 'Fuga de agua en Cerro Colorado', 'Desde hace 3 días hay una fuga de agua potable en la esquina de Av. Progreso con Calle Los Álamos. El agua se pierde constantemente.', -16.3850, -71.5600, 'pendiente', TRUE, 'aprobado'),
(2, 6, 1, 'Árbol caído por lluvias en Yanahuara', 'Las fuertes lluvias de anoche derribaron un árbol en la Plaza de Yanahuara. Bloquea parcialmente el paso vehicular.', -16.3950, -71.5200, 'pendiente', TRUE, 'aprobado'),
(1, 7, 1, 'Foco de infección en mercado San Camilo', 'Se detectaron condiciones insalubres en los puestos de comida del mercado San Camilo. Posible foco de enfermedades estomacales.', -16.4070, -71.5350, 'pendiente', TRUE, 'aprobado'),
(7, 8, 2, 'Semáforo dañado en Av. Dolores', 'El semáforo de la intersección de Av. Dolores con Calle Perú no funciona desde hace 4 días. Causa caos vehicular en horas punta.', -16.4100, -71.5300, 'en_proceso', TRUE, 'aprobado');

-- ============================================================
-- COMENTARIOS DE EJEMPLO
-- ============================================================
INSERT INTO comentarios (reporte_id, usuario_id, contenido, moderado, estado_moderacion) VALUES
(1, 2, 'He visto ese hueco, es realmente peligroso. Ya casi tengo un accidente ahí.', TRUE, 'aprobado'),
(1, 7, 'Apoyo el reporte. Deberían arreglarlo urgentemente.', TRUE, 'aprobado'),
(2, 1, 'He ido al parque y la situación es insostenible. El olor es muy fuerte.', TRUE, 'aprobado'),
(4, 8, 'Vivo en esa zona y confirmo. Los vecinos estamos organizando rondas.', TRUE, 'aprobado');

-- ============================================================
-- LIKES DE EJEMPLO
-- ============================================================
INSERT INTO likes (reporte_id, usuario_id) VALUES
(1, 2), (1, 7), (1, 8),
(2, 1), (2, 7),
(3, 1), (3, 8),
(4, 1), (4, 2), (4, 7);

-- ============================================================
-- NOTIFICACIONES DE EJEMPLO
-- ============================================================
INSERT INTO notificaciones (usuario_id, mensaje, tipo, referencia_id) VALUES
(1, 'Tu reporte "Hueco peligroso en Av. Ejército" ha sido recibido y está pendiente de revisión.', 'reporte_creado', 1),
(2, 'Tu reporte "Basura acumulada en Parque Cayma" ha sido asignado a un operador técnico.', 'reporte_asignado', 2);

-- ============================================================
-- CONTACTOS DE EMERGENCIA ADICIONALES
-- ============================================================
INSERT INTO emergencias_contactos (nombre_servicio, numero, descripcion) VALUES
('Emergencia Mujer', '100', 'Atención a víctimas de violencia de género.'),
('Línea 113', '113', 'Orientación en salud, COVID-19 y farmacia.');
