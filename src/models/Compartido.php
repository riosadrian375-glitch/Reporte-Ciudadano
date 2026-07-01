<?php

class Compartido {
    public static function registrar($reporte_id, $usuario_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("INSERT INTO compartidos (reporte_id, usuario_id) VALUES (?, ?)");
        return $stmt->execute([$reporte_id, $usuario_id]);
    }
}
