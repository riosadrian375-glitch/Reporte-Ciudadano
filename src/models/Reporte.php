<?php

class Reporte {
    public static function crear($datos) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("INSERT INTO reportes (usuario_id, categoria_id, distrito_id, titulo, descripcion, latitud, longitud, direccion, es_urgente, clima_momento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $datos['usuario_id'],
            $datos['categoria_id'],
            $datos['distrito_id'],
            $datos['titulo'],
            $datos['descripcion'],
            $datos['latitud'] ?? null,
            $datos['longitud'] ?? null,
            $datos['direccion'] ?? null,
            $datos['es_urgente'] ?? 0,
            $datos['clima_momento'] ?? null
        ]);
        return $db->lastInsertId();
    }

    public static function porId($id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("
            SELECT r.*, c.nombre as categoria_nombre, c.icono as categoria_icono,
                   d.nombre as distrito_nombre,
                   u.nombre as usuario_nombre, u.foto_perfil,
                   (SELECT COUNT(*) FROM likes WHERE reporte_id = r.id) as total_likes,
                   (SELECT COUNT(*) FROM comentarios WHERE reporte_id = r.id) as total_comentarios,
                   (SELECT COUNT(*) FROM compartidos WHERE reporte_id = r.id) as total_compartidos
            FROM reportes r
            JOIN categorias c ON r.categoria_id = c.id
            JOIN distritos d ON r.distrito_id = d.id
            JOIN usuarios u ON r.usuario_id = u.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function listar($filtros = []) {
        $db = Database::obtener()->getConexion();
        $sql = "
            SELECT r.*, c.nombre as categoria_nombre, c.icono as categoria_icono,
                   d.nombre as distrito_nombre,
                   u.nombre as usuario_nombre,
                   (SELECT COUNT(*) FROM likes WHERE reporte_id = r.id) as total_likes,
                   (SELECT COUNT(*) FROM comentarios WHERE reporte_id = r.id) as total_comentarios,
                   (SELECT COUNT(*) FROM compartidos WHERE reporte_id = r.id) as total_compartidos,
                   (SELECT ri.ruta_archivo FROM reporte_imagenes ri WHERE ri.reporte_id = r.id LIMIT 1) as imagen_thumbnail,
                   (SELECT rv.ruta_archivo FROM reporte_videos rv WHERE rv.reporte_id = r.id LIMIT 1) as video_thumbnail
            FROM reportes r
            JOIN categorias c ON r.categoria_id = c.id
            JOIN distritos d ON r.distrito_id = d.id
            JOIN usuarios u ON r.usuario_id = u.id
            WHERE r.estado_moderacion = 'aprobado'
        ";
        $params = [];

        if (!empty($filtros['categoria_id'])) {
            $sql .= " AND r.categoria_id = ?";
            $params[] = $filtros['categoria_id'];
        }
        if (!empty($filtros['distrito_id'])) {
            $sql .= " AND r.distrito_id = ?";
            $params[] = $filtros['distrito_id'];
        }
        if (!empty($filtros['estado'])) {
            $sql .= " AND r.estado = ?";
            $params[] = $filtros['estado'];
        }
        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND r.usuario_id = ?";
            $params[] = $filtros['usuario_id'];
        }
        if (!empty($filtros['buscar'])) {
            $sql .= " AND (r.titulo LIKE ? OR r.descripcion LIKE ?)";
            $params[] = '%' . $filtros['buscar'] . '%';
            $params[] = '%' . $filtros['buscar'] . '%';
        }

        if (!empty($filtros['priorizar_distrito'])) {
            $sql .= " ORDER BY (r.distrito_id = ?) DESC, ";
            $params[] = (int)$filtros['priorizar_distrito'];
        } else {
            $sql .= " ORDER BY ";
        }

        $orden = !empty($filtros['orden']) ? $filtros['orden'] : 'recientes';
        switch ($orden) {
            case 'antiguos':
                $sql .= "r.fecha_creacion ASC";
                break;
            case 'populares':
                $sql .= "total_likes DESC, r.fecha_creacion DESC";
                break;
            default:
                $sql .= "r.fecha_creacion DESC";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function listarTodosAdmin($filtros = []) {
        $db = Database::obtener()->getConexion();
        $sql = "
            SELECT r.*, c.nombre as categoria_nombre, c.icono as categoria_icono,
                   d.nombre as distrito_nombre,
                   u.nombre as usuario_nombre,
                   (SELECT COUNT(*) FROM likes WHERE reporte_id = r.id) as total_likes,
                   (SELECT COUNT(*) FROM comentarios WHERE reporte_id = r.id) as total_comentarios,
                   a.id as asignacion_id, a.operador_id, a.estado as asignacion_estado,
                   op.nombre as operador_nombre,
                   (SELECT ri.ruta_archivo FROM reporte_imagenes ri WHERE ri.reporte_id = r.id LIMIT 1) as imagen_thumbnail,
                   (SELECT rv.ruta_archivo FROM reporte_videos rv WHERE rv.reporte_id = r.id LIMIT 1) as video_thumbnail
            FROM reportes r
            JOIN categorias c ON r.categoria_id = c.id
            JOIN distritos d ON r.distrito_id = d.id
            JOIN usuarios u ON r.usuario_id = u.id
            LEFT JOIN asignaciones a ON r.id = a.reporte_id AND a.estado = 'activa'
            LEFT JOIN usuarios op ON a.operador_id = op.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filtros['categoria_id'])) {
            $sql .= " AND r.categoria_id = ?";
            $params[] = $filtros['categoria_id'];
        }
        if (!empty($filtros['distrito_id'])) {
            $sql .= " AND r.distrito_id = ?";
            $params[] = $filtros['distrito_id'];
        }
        if (!empty($filtros['estado'])) {
            $sql .= " AND r.estado = ?";
            $params[] = $filtros['estado'];
        }
        $sql .= " ORDER BY r.fecha_creacion DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function actualizarEstado($id, $estado) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("UPDATE reportes SET estado = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }

    public static function actualizar($id, $datos) {
        $db = Database::obtener()->getConexion();
        $campos = [];
        $params = [];
        foreach (['categoria_id', 'distrito_id', 'titulo', 'descripcion', 'latitud', 'longitud', 'direccion', 'es_urgente'] as $campo) {
            if (isset($datos[$campo])) {
                $campos[] = "$campo = ?";
                $params[] = $datos[$campo];
            }
        }
        if (empty($campos)) return false;
        $params[] = $id;
        $stmt = $db->prepare("UPDATE reportes SET " . implode(', ', $campos) . ", fecha_actualizacion = NOW() WHERE id = ?");
        return $stmt->execute($params);
    }

    public static function eliminar($id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("DELETE FROM reportes WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function imagenes($reporte_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("SELECT * FROM reporte_imagenes WHERE reporte_id = ?");
        $stmt->execute([$reporte_id]);
        return $stmt->fetchAll();
    }

    public static function videos($reporte_id) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("SELECT * FROM reporte_videos WHERE reporte_id = ?");
        $stmt->execute([$reporte_id]);
        return $stmt->fetchAll();
    }

    public static function agregarImagen($reporte_id, $ruta) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("INSERT INTO reporte_imagenes (reporte_id, ruta_archivo) VALUES (?, ?)");
        return $stmt->execute([$reporte_id, $ruta]);
    }

    public static function agregarVideo($reporte_id, $ruta, $duracion = null) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("INSERT INTO reporte_videos (reporte_id, ruta_archivo, duracion_segundos) VALUES (?, ?, ?)");
        return $stmt->execute([$reporte_id, $ruta, $duracion]);
    }

    public static function contarPorEstado() {
        $db = Database::obtener()->getConexion();
        $stmt = $db->query("SELECT estado, COUNT(*) as total FROM reportes GROUP BY estado");
        return $stmt->fetchAll();
    }

    public static function contarPorCategoria() {
        $db = Database::obtener()->getConexion();
        $stmt = $db->query("SELECT c.nombre, COUNT(*) as total FROM reportes r JOIN categorias c ON r.categoria_id = c.id GROUP BY r.categoria_id");
        return $stmt->fetchAll();
    }

    public static function marcarModeracion($id, $estado) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("UPDATE reportes SET moderado = TRUE, estado_moderacion = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }

    public static function reportesCercanos($lat, $lng, $limite = 50) {
        $db = Database::obtener()->getConexion();
        $stmt = $db->prepare("
            SELECT r.*, c.nombre as categoria_nombre, c.icono as categoria_icono,
                   d.nombre as distrito_nombre,
                   (SELECT COUNT(*) FROM likes WHERE reporte_id = r.id) as total_likes
            FROM reportes r
            JOIN categorias c ON r.categoria_id = c.id
            JOIN distritos d ON r.distrito_id = d.id
            WHERE r.latitud IS NOT NULL AND r.longitud IS NOT NULL
              AND r.estado_moderacion = 'aprobado'
            ORDER BY r.fecha_creacion DESC
            LIMIT ?
        ");
        $stmt->execute([$limite]);
        return $stmt->fetchAll();
    }
}
