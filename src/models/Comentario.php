<?php

class Comentario {
    public static function crear($reporte_id, $usuario_id, $contenido) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("INSERT INTO comentarios (reporte_id, usuario_id, contenido) VALUES (?, ?, ?)");
        $stmt->execute([$reporte_id, $usuario_id, $contenido]);
        return $db->lastInsertId();
    }

    public static function porReporte($reporte_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("
            SELECT c.*, u.nombre as usuario_nombre, u.foto_perfil
            FROM comentarios c
            JOIN usuarios u ON c.usuario_id = u.id
            WHERE c.reporte_id = ? AND c.estado_moderacion = 'aprobado'
            ORDER BY c.fecha ASC
        ");
        $stmt->execute([$reporte_id]);
        return $stmt->fetchAll();
    }

    public static function marcarModeracion($id, $estado) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("UPDATE comentarios SET moderado = TRUE, estado_moderacion = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }
}
