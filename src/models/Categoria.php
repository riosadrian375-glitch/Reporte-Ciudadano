<?php

class Categoria {
    public static function listar() {
        $db = Database::obtener()->getConexion();
        $stmt = $db->query("SELECT * FROM categorias ORDER BY nombre");
        return $stmt->fetchAll();
    }

    public static function porId($id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("SELECT * FROM categorias WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
