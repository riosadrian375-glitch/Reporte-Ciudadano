<?php

class Asignacion {
    public static function crear($reporte_id, $operador_id, $admin_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("INSERT INTO asignaciones (reporte_id, operador_id, admin_id) VALUES (?, ?, ?)");
        $stmt->execute([$reporte_id, $operador_id, $admin_id]);
        return $db->lastInsertId();
    }

    public static function porOperador($operador_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("
            SELECT a.*, r.titulo, r.descripcion, r.estado as reporte_estado,
                   r.latitud, r.longitud, r.direccion, r.clima_momento,
                   c.nombre as categoria_nombre, d.nombre as distrito_nombre,
                   u.nombre as ciudadano_nombre,
                   (SELECT COUNT(*) FROM likes WHERE reporte_id = r.id) as total_likes,
                   (SELECT ri.ruta_archivo FROM reporte_imagenes ri WHERE ri.reporte_id = r.id LIMIT 1) as imagen_thumbnail,
                   (SELECT rv.ruta_archivo FROM reporte_videos rv WHERE rv.reporte_id = r.id LIMIT 1) as video_thumbnail
            FROM asignaciones a
            JOIN reportes r ON a.reporte_id = r.id
            JOIN categorias c ON r.categoria_id = c.id
            JOIN distritos d ON r.distrito_id = d.id
            JOIN usuarios u ON r.usuario_id = u.id
            WHERE a.operador_id = ? AND a.estado = 'activa'
            ORDER BY a.fecha_asignacion DESC
        ");
        $stmt->execute([$operador_id]);
        return $stmt->fetchAll();
    }

    public static function activaPorReporte($reporte_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("SELECT a.*, u.nombre as operador_nombre FROM asignaciones a JOIN usuarios u ON a.operador_id = u.id WHERE a.reporte_id = ? AND a.estado = 'activa'");
        $stmt->execute([$reporte_id]);
        return $stmt->fetch();
    }

    public static function completar($id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("UPDATE asignaciones SET estado = 'completada' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function cancelar($id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("UPDATE asignaciones SET estado = 'cancelada' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function listarTodas() {
        $db = Database::obtener()->getConexion();
        $stmt = $db->query("
            SELECT a.*, r.titulo as reporte_titulo, u.nombre as operador_nombre, adm.nombre as admin_nombre
            FROM asignaciones a
            JOIN reportes r ON a.reporte_id = r.id
            JOIN usuarios u ON a.operador_id = u.id
            JOIN usuarios adm ON a.admin_id = adm.id
            ORDER BY a.fecha_asignacion DESC
        ");
        return $stmt->fetchAll();
    }
}
