<?php

class Usuario {
    public static function registrar($datos) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("INSERT INTO usuarios (nombre, correo, password_hash, rol, distrito_id) VALUES (?, ?, ?, 'ciudadano', ?)");
        $hash = password_hash($datos['password'], PASSWORD_DEFAULT);
        $stmt->execute([$datos['nombre'], $datos['correo'], $hash, $datos['distrito_id'] ?? null]);
        return $db->lastInsertId();
    }

    public static function porCorreo($correo) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("SELECT u.*, d.nombre as distrito_nombre FROM usuarios u LEFT JOIN distritos d ON u.distrito_id = d.id WHERE u.correo = ?");
        $stmt->execute([$correo]);
        return $stmt->fetch();
    }

    public static function porId($id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("SELECT u.*, d.nombre as distrito_nombre FROM usuarios u LEFT JOIN distritos d ON u.distrito_id = d.id WHERE u.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function listar($rol = null, $estado = 'activo') {
        $db = Database::obtener()->getConexion();
        $sql = "SELECT u.*, d.nombre as distrito_nombre FROM usuarios u LEFT JOIN distritos d ON u.distrito_id = d.id WHERE u.estado = ?";
        $params = [$estado];
        if ($rol) {
            $sql .= " AND u.rol = ?";
            $params[] = $rol;
        }
        $sql .= " ORDER BY u.fecha_registro DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function listarTodos() {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("SELECT u.*, d.nombre as distrito_nombre FROM usuarios u LEFT JOIN distritos d ON u.distrito_id = d.id ORDER BY u.fecha_registro DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function listarOperadoresConCarga() {
        $db = Database::obtener()->getConexion();
        $stmt = $db->query("
            SELECT u.*, d.nombre AS distrito_nombre,
                   COUNT(a.id) AS carga_actual
            FROM usuarios u
            LEFT JOIN distritos d ON u.distrito_id = d.id
            LEFT JOIN asignaciones a ON a.operador_id = u.id AND a.estado = 'activa'
            WHERE u.rol = 'operador' AND u.estado = 'activo'
            GROUP BY u.id
            ORDER BY carga_actual ASC, u.nombre ASC
        ");
        return $stmt->fetchAll();
    }

    public static function actualizar($id, $datos) {
        $db = Database::obtener()->getConexion();
        $campos = [];
        $params = [];
        foreach (['nombre', 'correo', 'rol', 'distrito_id', 'estado'] as $campo) {
            if (isset($datos[$campo])) {
                $campos[] = "$campo = ?";
                $params[] = $datos[$campo];
            }
        }
        if (isset($datos['password'])) {
            $campos[] = "password_hash = ?";
            $params[] = password_hash($datos['password'], PASSWORD_DEFAULT);
        }
        if (empty($campos)) return false;
        $params[] = $id;
        $stmt = $db->prepare("UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public static function eliminar($id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("UPDATE usuarios SET estado = 'eliminado' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function suspender($id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("UPDATE usuarios SET estado = 'inactivo' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function activar($id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("UPDATE usuarios SET estado = 'activo' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function actualizarUltimoAcceso($id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
        $stmt->execute([$id]);
    }
}
