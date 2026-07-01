<?php

class EmergenciaContacto {
    public static function listar() {
        $db = Database::obtener()->getConexion();
        $stmt = $db->query("SELECT * FROM emergencias_contactos ORDER BY id");
        return $stmt->fetchAll();
    }
}
