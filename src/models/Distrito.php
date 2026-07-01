<?php

class Distrito {
    public static function listar() {
        $db = Database::obtener()->getConexion();
        $stmt = $db->query("SELECT * FROM distritos ORDER BY nombre");
        return $stmt->fetchAll();
    }

    public static function porId($id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("SELECT * FROM distritos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
