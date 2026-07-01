<?php

class Like {
    public static function toggle($reporte_id, $usuario_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("SELECT id FROM likes WHERE reporte_id = ? AND usuario_id = ?");
        $stmt->execute([$reporte_id, $usuario_id]);
        if ($stmt->fetch()) {
            $stmt = $db->prepare("DELETE FROM likes WHERE reporte_id = ? AND usuario_id = ?");
            $stmt->execute([$reporte_id, $usuario_id]);
            return ['action' => 'unliked'];
        } else {
            $stmt = $db->prepare("INSERT INTO likes (reporte_id, usuario_id) VALUES (?, ?)");
            $stmt->execute([$reporte_id, $usuario_id]);
            return ['action' => 'liked'];
        }
    }

    public static function usuarioDioLike($reporte_id, $usuario_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("SELECT id FROM likes WHERE reporte_id = ? AND usuario_id = ?");
        $stmt->execute([$reporte_id, $usuario_id]);
        return $stmt->fetch() ? true : false;
    }
}
