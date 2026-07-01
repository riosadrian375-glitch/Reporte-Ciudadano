<?php

class ActividadLog {
    public static function registrar($usuarioId, $accion, $modulo = 'api', $estado = 'ok', $detalle = null) {
        try {
            $db = Database::obtener()->getConexion();
            $stmt = $db->prepare("
                INSERT INTO actividad_logs
                    (usuario_id, accion, modulo, estado, detalle, direccion_ip)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $usuarioId ?: null,
                mb_substr((string)$accion, 0, 120),
                mb_substr((string)$modulo, 0, 60),
                mb_substr((string)$estado, 0, 30),
                $detalle,
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public static function listar($limite = 150, $busqueda = '') {
        try {
            $db = Database::obtener()->getConexion();
            $limite = max(1, min(500, (int)$limite));
            $sql = "
                SELECT l.*, u.nombre AS usuario_nombre, u.correo AS usuario_correo
                FROM actividad_logs l
                LEFT JOIN usuarios u ON u.id = l.usuario_id
            ";
            $params = [];
            if ($busqueda !== '') {
                $sql .= " WHERE l.accion LIKE ? OR l.modulo LIKE ? OR l.detalle LIKE ? ";
                $like = '%' . $busqueda . '%';
                $params = [$like, $like, $like];
            }
            $sql .= " ORDER BY l.fecha DESC LIMIT {$limite}";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }
}
