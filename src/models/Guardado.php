<?php

class Guardado {
    public static function toggle($reporte_id, $usuario_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("SELECT id FROM guardados WHERE reporte_id = ? AND usuario_id = ?");
        $stmt->execute([$reporte_id, $usuario_id]);
        if ($stmt->fetch()) {
            $stmt = $db->prepare("DELETE FROM guardados WHERE reporte_id = ? AND usuario_id = ?");
            $stmt->execute([$reporte_id, $usuario_id]);
            return ['action' => 'unsaved'];
        } else {
            $stmt = $db->prepare("INSERT INTO guardados (reporte_id, usuario_id) VALUES (?, ?)");
            $stmt->execute([$reporte_id, $usuario_id]);
            return ['action' => 'saved'];
        }
    }

    public static function usuarioGuardo($reporte_id, $usuario_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("SELECT id FROM guardados WHERE reporte_id = ? AND usuario_id = ?");
        $stmt->execute([$reporte_id, $usuario_id]);
        return $stmt->fetch() ? true : false;
    }

    public static function listarPorUsuario($usuario_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("
            SELECT r.*, c.nombre as categoria_nombre, c.icono as categoria_icono,
                   d.nombre as distrito_nombre, g.fecha as fecha_guardado,
                   (SELECT COUNT(*) FROM likes WHERE reporte_id = r.id) as total_likes,
                   (SELECT COUNT(*) FROM comentarios WHERE reporte_id = r.id) as total_comentarios
            FROM guardados g
            JOIN reportes r ON g.reporte_id = r.id
            JOIN categorias c ON r.categoria_id = c.id
            JOIN distritos d ON r.distrito_id = d.id
            WHERE g.usuario_id = ?
            ORDER BY g.fecha DESC
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll();
    }
}
