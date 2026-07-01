<?php

class MantenimientoService {
    private $directorioBackups;

    public function __construct() {
        $this->directorioBackups = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';
    }

    public function generarBackup() {
        if (!is_dir($this->directorioBackups) && !mkdir($this->directorioBackups, 0750, true)) {
            throw new RuntimeException('No se pudo crear el directorio de backups.');
        }

        $db = Database::obtener()->getConexion();
        $nombre = 'reporteciudadano_' . date('Ymd_His') . '.sql';
        $ruta = $this->directorioBackups . DIRECTORY_SEPARATOR . $nombre;
        $temporal = $ruta . '.tmp';
        $handle = fopen($temporal, 'wb');
        if (!$handle) {
            throw new RuntimeException('No se pudo crear el archivo de backup.');
        }

        try {
            fwrite($handle, "-- Backup ReporteCiudadano\n-- Generado: " . date('c') . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n");
            $tablas = $db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_NUM);
            foreach ($tablas as $filaTabla) {
                $tabla = $filaTabla[0];
                $identificador = '`' . str_replace('`', '``', $tabla) . '`';
                $crear = $db->query("SHOW CREATE TABLE {$identificador}")->fetch(PDO::FETCH_NUM);
                fwrite($handle, "DROP TABLE IF EXISTS {$identificador};\n{$crear[1]};\n\n");

                $stmt = $db->query("SELECT * FROM {$identificador}");
                while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $columnas = array_map(function ($columna) {
                        return '`' . str_replace('`', '``', $columna) . '`';
                    }, array_keys($fila));
                    $valores = array_map(function ($valor) use ($db) {
                        return $valor === null ? 'NULL' : $db->quote((string)$valor);
                    }, array_values($fila));
                    fwrite($handle, "INSERT INTO {$identificador} (" . implode(', ', $columnas) . ") VALUES (" . implode(', ', $valores) . ");\n");
                }
                fwrite($handle, "\n");
            }
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        } finally {
            fclose($handle);
        }

        if (!rename($temporal, $ruta)) {
            @unlink($temporal);
            throw new RuntimeException('No se pudo finalizar el archivo de backup.');
        }

        return [
            'nombre' => $nombre,
            'tamano' => filesize($ruta),
            'fecha' => date('Y-m-d H:i:s', filemtime($ruta)),
        ];
    }

    public function listarBackups() {
        if (!is_dir($this->directorioBackups)) {
            return [];
        }
        $archivos = glob($this->directorioBackups . DIRECTORY_SEPARATOR . '*.sql') ?: [];
        usort($archivos, function ($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });
        return array_map(function ($ruta) {
            return [
                'nombre' => basename($ruta),
                'tamano' => filesize($ruta),
                'fecha' => date('Y-m-d H:i:s', filemtime($ruta)),
            ];
        }, $archivos);
    }
}
