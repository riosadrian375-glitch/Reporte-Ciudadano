<?php

class ReporteEvidencia {
    public static function crear($reporteId, $operadorId, $comentario, $rutaArchivo, $tipoMime) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("
            INSERT INTO reporte_evidencias
                (reporte_id, operador_id, comentario_resolucion, ruta_archivo, tipo_mime)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$reporteId, $operadorId, $comentario, $rutaArchivo, $tipoMime]);
        return $db->lastInsertId();
    }

    public static function porReporte($reporteId) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("
            SELECT e.*, u.nombre AS operador_nombre
            FROM reporte_evidencias e
            JOIN usuarios u ON u.id = e.operador_id
            WHERE e.reporte_id = ?
            ORDER BY e.fecha DESC
        ");
        $stmt->execute([$reporteId]);
        return $stmt->fetchAll();
    }
}
