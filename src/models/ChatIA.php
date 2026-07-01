<?php

class ChatIA {
    public static function crearConversacion($usuario_id, $reporte_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("INSERT INTO chat_ia_conversaciones (usuario_id, reporte_id) VALUES (?, ?)");
        $stmt->execute([$usuario_id, $reporte_id]);
        return $db->lastInsertId();
    }

    public static function obtenerConversacion($usuario_id, $reporte_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("SELECT * FROM chat_ia_conversaciones WHERE usuario_id = ? AND reporte_id = ? ORDER BY fecha_inicio DESC LIMIT 1");
        $stmt->execute([$usuario_id, $reporte_id]);
        return $stmt->fetch();
    }

    public static function guardarMensaje($conversacion_id, $remitente, $mensaje) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("INSERT INTO chat_ia_mensajes (conversacion_id, remitente, mensaje) VALUES (?, ?, ?)");
        return $stmt->execute([$conversacion_id, $remitente, $mensaje]);
    }

    public static function historial($conversacion_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("SELECT * FROM chat_ia_mensajes WHERE conversacion_id = ? ORDER BY fecha ASC");
        $stmt->execute([$conversacion_id]);
        return $stmt->fetchAll();
    }
}
