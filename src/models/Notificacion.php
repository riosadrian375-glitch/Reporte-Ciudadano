<?php

class Notificacion {
    public static function crear($usuario_id, $mensaje, $tipo = 'general', $referencia_id = null) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("INSERT INTO notificaciones (usuario_id, mensaje, tipo, referencia_id) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$usuario_id, $mensaje, $tipo, $referencia_id]);
    }

    public static function noLeidas($usuario_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("SELECT * FROM notificaciones WHERE usuario_id = ? AND leida = FALSE ORDER BY fecha DESC");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll();
    }

    public static function todas($usuario_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY fecha DESC LIMIT 50");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll();
    }

    public static function contarNoLeidas($usuario_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM notificaciones WHERE usuario_id = ? AND leida = FALSE");
        $stmt->execute([$usuario_id]);
        return $stmt->fetch()['total'];
    }

    public static function marcarLeida($id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("UPDATE notificaciones SET leida = TRUE WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function marcarTodasLeidas($usuario_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("UPDATE notificaciones SET leida = TRUE WHERE usuario_id = ? AND leida = FALSE");
        return $stmt->execute([$usuario_id]);
    }
}
