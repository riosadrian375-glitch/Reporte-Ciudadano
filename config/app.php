<?php

define('DB_HOST', getenv('RC_DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('RC_DB_PORT') ?: '3306');
define('DB_NAME', getenv('RC_DB_NAME') ?: 'reporteciudadano');
define('DB_USER', getenv('RC_DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('RC_DB_PASSWORD') ?: '');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'ReporteCiudadano');
define('SITE_DESCRIPTION', 'Red social de reportes ciudadanos — Arequipa, Perú');
function rc_detect_site_url() {
    if (PHP_SAPI === 'cli') {
        return 'http://localhost';
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $basePath = str_replace('\\', '/', dirname($scriptName));

    if ($basePath === '/' || $basePath === '.') {
        $basePath = '';
    }

    return rtrim($scheme . '://' . $host . $basePath, '/');
}

define('SITE_URL', rtrim(getenv('RC_SITE_URL') ?: rc_detect_site_url(), '/'));

define('UPLOAD_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads');
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);
define('MAX_VIDEO_SIZE', 50 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm']);

session_start();

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/api_keys.php';

require_once dirname(__DIR__) . '/src/models/Usuario.php';
require_once dirname(__DIR__) . '/src/models/Reporte.php';
require_once dirname(__DIR__) . '/src/models/Comentario.php';
require_once dirname(__DIR__) . '/src/models/Like.php';
require_once dirname(__DIR__) . '/src/models/Guardado.php';
require_once dirname(__DIR__) . '/src/models/Compartido.php';
require_once dirname(__DIR__) . '/src/models/Asignacion.php';
require_once dirname(__DIR__) . '/src/models/Notificacion.php';
require_once dirname(__DIR__) . '/src/models/Categoria.php';
require_once dirname(__DIR__) . '/src/models/Distrito.php';
require_once dirname(__DIR__) . '/src/models/ChatIA.php';
require_once dirname(__DIR__) . '/src/models/EmergenciaContacto.php';
require_once dirname(__DIR__) . '/src/models/ReporteEvidencia.php';
require_once dirname(__DIR__) . '/src/models/ActividadLog.php';

require_once dirname(__DIR__) . '/src/services/ClimaService.php';
require_once dirname(__DIR__) . '/src/services/ModeracionService.php';
require_once dirname(__DIR__) . '/src/services/ChatIAService.php';
require_once dirname(__DIR__) . '/src/services/MantenimientoService.php';

function estaLogueado() {
    return isset($_SESSION['usuario_id']);
}

function esRol($rol) {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === $rol;
}

function redirigirSiNoLogueado() {
    if (!estaLogueado()) {
        header('Location: ' . SITE_URL . '/index.php?view=login');
        exit;
    }
}

function redirigirSiNoEsRol($rol) {
    redirigirSiNoLogueado();
    if (!esRol($rol)) {
        header('Location: ' . SITE_URL . '/index.php?view=feed');
        exit;
    }
}

function vistaPrincipalPorRol($rol = null) {
    $rol = $rol ?? ($_SESSION['usuario_rol'] ?? 'ciudadano');
    $vistas = [
        'ciudadano' => 'ciudadano_dashboard',
        'admin_municipal' => 'admin_dashboard',
        'operador' => 'operador_dashboard',
        'admin_sistema' => 'sistema_dashboard',
    ];
    return $vistas[$rol] ?? 'feed';
}

function tiempoRelativo($fecha) {
    if (empty($fecha)) return '';
    $timestamp = strtotime($fecha);
    $diferencia = time() - $timestamp;
    if ($diferencia < 60) return 'hace ' . $diferencia . ' segundos';
    if ($diferencia < 3600) return 'hace ' . floor($diferencia / 60) . ' minutos';
    if ($diferencia < 86400) return 'hace ' . floor($diferencia / 3600) . ' horas';
    if ($diferencia < 604800) return 'hace ' . floor($diferencia / 86400) . ' dias';
    if ($diferencia < 2592000) return 'hace ' . floor($diferencia / 604800) . ' semanas';
    return date('d/m/Y', $timestamp);
}

function resumirTexto($texto, $limite = 120) {
    if (mb_strlen($texto) <= $limite) return $texto;
    return mb_substr($texto, 0, $limite) . '...';
}
