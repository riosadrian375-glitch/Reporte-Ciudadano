USE reporteciudadano;

ALTER TABLE usuarios
    MODIFY estado ENUM('activo','inactivo','suspendido','eliminado') NOT NULL DEFAULT 'activo';

ALTER TABLE reportes
    ADD COLUMN IF NOT EXISTS clima_momento JSON DEFAULT NULL AFTER es_urgente;

CREATE TABLE IF NOT EXISTS reporte_evidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporte_id INT NOT NULL,
    operador_id INT NOT NULL,
    comentario_resolucion TEXT NOT NULL,
    ruta_archivo VARCHAR(300) NOT NULL,
    tipo_mime VARCHAR(100) NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporte_id) REFERENCES reportes(id) ON DELETE CASCADE,
    FOREIGN KEY (operador_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS actividad_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT DEFAULT NULL,
    accion VARCHAR(120) NOT NULL,
    modulo VARCHAR(60) NOT NULL DEFAULT 'api',
    estado VARCHAR(30) NOT NULL DEFAULT 'ok',
    detalle TEXT DEFAULT NULL,
    direccion_ip VARCHAR(45) DEFAULT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_actividad_fecha (fecha),
    INDEX idx_actividad_accion (accion),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
