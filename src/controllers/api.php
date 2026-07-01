<?php

header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__, 2) . '/config/app.php';

$action = isset($_GET['action']) ? preg_replace('/[^a-zA-Z0-9_\/\-]/', '', $_GET['action']) : '';
$action = str_replace('/', '_', $action);
$usuario_id = $_SESSION['usuario_id'] ?? null;

function registrarActividadApi($estado, $detalle = null) {
    global $action, $usuario_id;
    if (in_array($action, ['sistema_logs', 'notificaciones_listar'], true)) {
        return;
    }
    ActividadLog::registrar(
        $usuario_id,
        $action ?: 'accion_desconocida',
        str_starts_with($action, 'chat_ia') ? 'ia' : (str_starts_with($action, 'clima') ? 'api_externa' : 'api'),
        $estado,
        $detalle
    );
}

function jsonError($mensaje, $status = 400) {
    registrarActividadApi('error', $mensaje);
    http_response_code($status);
    echo json_encode(['success' => false, 'error' => $mensaje], JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonSuccess($datos = []) {
    registrarActividadApi('ok');
    echo json_encode(['success' => true] + $datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function requerirPost() {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        jsonError('Esta acción requiere una solicitud POST.', 405);
    }
}

function guardarArchivoSeguro($archivo, $subdirectorio, $prefijo, $permitidos, $tamanoMaximo) {
    if (!isset($archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
        jsonError('No se recibió el archivo requerido o la carga falló.', 422);
    }
    if (($archivo['size'] ?? 0) <= 0 || $archivo['size'] > $tamanoMaximo) {
        jsonError('El archivo supera el tamaño permitido.', 422);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($archivo['tmp_name']);
    if (!$mime || !isset($permitidos[$mime])) {
        jsonError('El formato del archivo no está permitido.', 422);
    }

    $directorio = UPLOAD_PATH . DIRECTORY_SEPARATOR . $subdirectorio;
    if (!is_dir($directorio) && !mkdir($directorio, 0755, true)) {
        jsonError('No se pudo preparar el directorio de archivos.', 500);
    }

    $nombre = $prefijo . bin2hex(random_bytes(12)) . '.' . $permitidos[$mime];
    $destino = $directorio . DIRECTORY_SEPARATOR . $nombre;
    if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
        jsonError('No se pudo guardar el archivo.', 500);
    }

    return [
        'ruta' => 'uploads/' . str_replace('\\', '/', $subdirectorio) . '/' . $nombre,
        'mime' => $mime,
    ];
}

try {
    switch ($action) {
        case 'auth_login':
            requerirPost();
            $correo = trim($_POST['correo'] ?? '');
            $password = $_POST['password'] ?? '';
            if ($correo === '' || $password === '') jsonError('Correo y contraseña requeridos.', 422);
            $usuario = Usuario::porCorreo($correo);
            if (!$usuario || $usuario['estado'] !== 'activo' || !password_verify($password, $usuario['password_hash'])) {
                jsonError('Credenciales inválidas o cuenta inactiva.', 401);
            }
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            $_SESSION['usuario_distrito'] = $usuario['distrito_id'];
            $usuario_id = $usuario['id'];
            Usuario::actualizarUltimoAcceso($usuario['id']);
            jsonSuccess(['message' => 'Inicio de sesión exitoso.', 'rol' => $usuario['rol']]);

        case 'auth_register':
            requerirPost();
            $nombre = trim($_POST['nombre'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $password = $_POST['password'] ?? '';
            $distrito_id = (int)($_POST['distrito_id'] ?? 0);
            if ($nombre === '' || $correo === '' || $password === '' || !$distrito_id) jsonError('Todos los campos son obligatorios.', 422);
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) jsonError('Correo electrónico inválido.', 422);
            if (strlen($password) < 6) jsonError('La contraseña debe tener al menos 6 caracteres.', 422);
            if (!Distrito::porId($distrito_id)) jsonError('Distrito inválido.', 422);
            if (Usuario::porCorreo($correo)) jsonError('El correo ya está registrado.', 409);
            $id = Usuario::registrar(compact('nombre', 'correo', 'password', 'distrito_id'));
            $_SESSION['usuario_id'] = $id;
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['usuario_rol'] = 'ciudadano';
            $_SESSION['usuario_distrito'] = $distrito_id;
            $usuario_id = $id;
            jsonSuccess(['message' => 'Registro exitoso.', 'rol' => 'ciudadano']);

        case 'reportes_listar':
            $filtros = [];
            if (!empty($_GET['categoria_id'])) $filtros['categoria_id'] = (int)$_GET['categoria_id'];
            if (!empty($_GET['distrito_id'])) $filtros['distrito_id'] = (int)$_GET['distrito_id'];
            if (!empty($_GET['estado'])) $filtros['estado'] = $_GET['estado'];
            if (!empty($_GET['orden'])) $filtros['orden'] = $_GET['orden'];
            if (!empty($_GET['buscar'])) $filtros['buscar'] = trim($_GET['buscar']);
            if (!empty($_GET['priorizar_distrito']) && !empty($_SESSION['usuario_distrito'])) {
                $filtros['priorizar_distrito'] = (int)$_SESSION['usuario_distrito'];
            }
            $reportes = Reporte::listar($filtros);
            foreach ($reportes as &$reporteListado) {
                $reporteListado['tiene_imagen'] = !empty($reporteListado['imagen_thumbnail']);
                $reporteListado['es_urgente'] = (bool)$reporteListado['es_urgente'];
            }
            unset($reporteListado);
            jsonSuccess(['reportes' => $reportes]);

        case 'reportes_detalle':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) jsonError('ID de reporte requerido.', 422);
            $reporte = Reporte::porId($id);
            if (!$reporte) jsonError('Reporte no encontrado.', 404);
            $reporte['imagenes'] = Reporte::imagenes($id);
            $reporte['videos'] = Reporte::videos($id);
            $reporte['comentarios'] = Comentario::porReporte($id);
            $reporte['evidencias'] = ReporteEvidencia::porReporte($id);
            $reporte['tiene_like'] = $usuario_id ? Like::usuarioDioLike($id, $usuario_id) : false;
            $reporte['tiene_guardado'] = $usuario_id ? Guardado::usuarioGuardo($id, $usuario_id) : false;
            jsonSuccess(['reporte' => $reporte]);

        case 'reportes_crear':
            requerirPost();
            if (!$usuario_id || !esRol('ciudadano')) jsonError('Debes iniciar sesión como ciudadano.', 401);
            $categoria_id = (int)($_POST['categoria_id'] ?? 0);
            $distrito_id = (int)($_POST['distrito_id'] ?? 0);
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            if (!$categoria_id || !$distrito_id || $titulo === '' || $descripcion === '') jsonError('Completa los campos obligatorios.', 422);

            $moderacion = (new ModeracionService())->moderarTexto($titulo . "\n" . $descripcion);
            if (empty($moderacion['aprobado'])) jsonError($moderacion['razon'] ?? 'El contenido no superó la moderación.', 422);

            $latitud = $_POST['latitud'] ?? null;
            $longitud = $_POST['longitud'] ?? null;
            $distrito = Distrito::porId($distrito_id);
            if (!$distrito) jsonError('Distrito inválido.', 422);
            $climaService = new ClimaService();
            $clima = ($latitud !== null && $longitud !== null)
                ? $climaService->obtenerPorCoordenadas($latitud, $longitud)
                : $climaService->obtenerPorDistrito($distrito['nombre']);

            $reporte_id = Reporte::crear([
                'usuario_id' => $usuario_id,
                'categoria_id' => $categoria_id,
                'distrito_id' => $distrito_id,
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'latitud' => $latitud,
                'longitud' => $longitud,
                'direccion' => trim($_POST['direccion'] ?? ''),
                'es_urgente' => !empty($_POST['es_urgente']) ? 1 : 0,
                'clima_momento' => json_encode($clima, JSON_UNESCAPED_UNICODE),
            ]);
            Reporte::marcarModeracion($reporte_id, 'aprobado');

            if (!empty($_FILES['imagenes']['name'][0])) {
                $archivos = $_FILES['imagenes'];
                for ($i = 0, $total = count($archivos['name']); $i < $total; $i++) {
                    $archivo = [
                        'name' => $archivos['name'][$i],
                        'type' => $archivos['type'][$i],
                        'tmp_name' => $archivos['tmp_name'][$i],
                        'error' => $archivos['error'][$i],
                        'size' => $archivos['size'][$i],
                    ];
                    $guardado = guardarArchivoSeguro($archivo, 'images', 'img_', [
                        'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'
                    ], MAX_IMAGE_SIZE);
                    Reporte::agregarImagen($reporte_id, $guardado['ruta']);
                }
            }
            if (!empty($_FILES['video']['name'])) {
                $guardado = guardarArchivoSeguro($_FILES['video'], 'videos', 'vid_', [
                    'video/mp4' => 'mp4', 'video/webm' => 'webm'
                ], MAX_VIDEO_SIZE);
                Reporte::agregarVideo($reporte_id, $guardado['ruta'], null);
            }
            Notificacion::crear($usuario_id, 'Tu reporte fue creado y aprobado por la moderación.', 'reporte_creado', $reporte_id);
            jsonSuccess(['reporte_id' => $reporte_id, 'message' => 'Reporte creado exitosamente.']);

        case 'likes_toggle':
            requerirPost();
            if (!$usuario_id) jsonError('Debes iniciar sesión.', 401);
            $reporte_id = (int)($_POST['reporte_id'] ?? 0);
            if (!$reporte_id) jsonError('Reporte requerido.', 422);
            $resultado = Like::toggle($reporte_id, $usuario_id);
            $stmt = Database::obtener()->getConexion()->prepare('SELECT COUNT(*) AS total FROM likes WHERE reporte_id = ?');
            $stmt->execute([$reporte_id]);
            jsonSuccess(['action' => $resultado['action'], 'total' => (int)$stmt->fetch()['total']]);

        case 'guardados_toggle':
            requerirPost();
            if (!$usuario_id) jsonError('Debes iniciar sesión.', 401);
            $reporte_id = (int)($_POST['reporte_id'] ?? 0);
            if (!$reporte_id) jsonError('Reporte requerido.', 422);
            $resultado = Guardado::toggle($reporte_id, $usuario_id);
            jsonSuccess(['action' => $resultado['action']]);

        case 'compartidos_crear':
            requerirPost();
            if (!$usuario_id) jsonError('Debes iniciar sesión.', 401);
            $reporte_id = (int)($_POST['reporte_id'] ?? 0);
            if (!$reporte_id) jsonError('Reporte requerido.', 422);
            Compartido::registrar($reporte_id, $usuario_id);
            $stmt = Database::obtener()->getConexion()->prepare('SELECT COUNT(*) AS total FROM compartidos WHERE reporte_id = ?');
            $stmt->execute([$reporte_id]);
            jsonSuccess(['message' => 'Compartido registrado.', 'total' => (int)$stmt->fetch()['total']]);

        case 'comentarios_crear':
            requerirPost();
            if (!$usuario_id) jsonError('Debes iniciar sesión.', 401);
            $reporte_id = (int)($_POST['reporte_id'] ?? 0);
            $contenido = trim($_POST['contenido'] ?? '');
            if (!$reporte_id || $contenido === '') jsonError('Reporte y contenido requeridos.', 422);
            $moderacion = (new ModeracionService())->moderarTexto($contenido);
            if (empty($moderacion['aprobado'])) jsonError($moderacion['razon'] ?? 'Comentario rechazado por la moderación.', 422);
            $comentario_id = Comentario::crear($reporte_id, $usuario_id, $contenido);
            Comentario::marcarModeracion($comentario_id, 'aprobado');
            $reporte = Reporte::porId($reporte_id);
            if ($reporte && (int)$reporte['usuario_id'] !== (int)$usuario_id) {
                Notificacion::crear($reporte['usuario_id'], ($_SESSION['usuario_nombre'] ?? 'Un usuario') . ' comentó en tu reporte "' . $reporte['titulo'] . '".', 'comentario', $reporte_id);
            }
            jsonSuccess(['comentario_id' => $comentario_id, 'message' => 'Comentario aprobado y agregado.']);

        case 'comentarios_listar':
            $reporte_id = (int)($_GET['reporte_id'] ?? 0);
            if (!$reporte_id) jsonError('Reporte requerido.', 422);
            jsonSuccess(['comentarios' => Comentario::porReporte($reporte_id)]);

        case 'asignar_reporte':
        case 'admin_asignar':
            requerirPost();
            if (!esRol('admin_municipal')) jsonError('No autorizado.', 403);
            $reporte_id = (int)($_POST['reporte_id'] ?? 0);
            $operador_id = (int)($_POST['operador_id'] ?? 0);
            if (!$reporte_id || !$operador_id) jsonError('Reporte y operador requeridos.', 422);
            $reporte = Reporte::porId($reporte_id);
            $operador = Usuario::porId($operador_id);
            if (!$reporte || $reporte['estado'] !== 'pendiente') jsonError('El reporte ya no está pendiente.', 409);
            if (!$operador || $operador['rol'] !== 'operador' || $operador['estado'] !== 'activo') jsonError('El operador no está disponible.', 409);
            if (Asignacion::activaPorReporte($reporte_id)) jsonError('El reporte ya tiene una asignación activa.', 409);

            $db = Database::obtener()->getConexion();
            $db->beginTransaction();
            try {
                Asignacion::crear($reporte_id, $operador_id, $usuario_id);
                Reporte::actualizarEstado($reporte_id, 'en_proceso');
                Notificacion::crear($operador_id, 'Se te ha asignado un nuevo reporte.', 'asignacion', $reporte_id);
                $db->commit();
            } catch (Throwable $e) {
                if ($db->inTransaction()) $db->rollBack();
                throw $e;
            }
            jsonSuccess(['message' => 'Reporte asignado exitosamente.']);

        case 'admin_operadores':
            if (!esRol('admin_municipal')) jsonError('No autorizado.', 403);
            jsonSuccess(['operadores' => Usuario::listarOperadoresConCarga()]);

        case 'actualizar_estado_reporte':
        case 'operador_cambiar_estado':
            requerirPost();
            if (!esRol('operador')) jsonError('No autorizado.', 403);
            $reporte_id = (int)($_POST['reporte_id'] ?? 0);
            $nuevo_estado = $_POST['estado'] ?? '';
            $comentario = trim($_POST['comentario_resolucion'] ?? '');
            if (!$reporte_id || !in_array($nuevo_estado, ['en_proceso', 'resuelto'], true)) jsonError('Estado inválido.', 422);
            if ($comentario === '') jsonError('El comentario de resolución es obligatorio.', 422);
            if (empty($_FILES['evidencia']['name'])) jsonError('La evidencia visual de cierre es obligatoria.', 422);
            $asignacion = Asignacion::activaPorReporte($reporte_id);
            if (!$asignacion || (int)$asignacion['operador_id'] !== (int)$usuario_id) jsonError('No tienes este reporte asignado.', 403);
            $moderacion = (new ModeracionService())->moderarTexto($comentario);
            if (empty($moderacion['aprobado'])) jsonError($moderacion['razon'] ?? 'Comentario rechazado por la moderación.', 422);
            $evidencia = guardarArchivoSeguro($_FILES['evidencia'], 'evidencias', 'cierre_', [
                'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'
            ], MAX_IMAGE_SIZE);

            $db = Database::obtener()->getConexion();
            $db->beginTransaction();
            try {
                ReporteEvidencia::crear($reporte_id, $usuario_id, $comentario, $evidencia['ruta'], $evidencia['mime']);
                Reporte::actualizarEstado($reporte_id, $nuevo_estado);
                if ($nuevo_estado === 'resuelto') Asignacion::completar($asignacion['id']);
                $reporte = Reporte::porId($reporte_id);
                Notificacion::crear($reporte['usuario_id'], 'El estado de tu reporte "' . $reporte['titulo'] . '" cambió a ' . str_replace('_', ' ', $nuevo_estado) . '.', 'estado', $reporte_id);
                $db->commit();
            } catch (Throwable $e) {
                if ($db->inTransaction()) $db->rollBack();
                throw $e;
            }
            jsonSuccess(['message' => 'Estado, comentario y evidencia registrados.']);

        case 'notificaciones_listar':
            if (!$usuario_id) jsonError('Debes iniciar sesión.', 401);
            jsonSuccess([
                'no_leidas' => Notificacion::noLeidas($usuario_id),
                'todas' => Notificacion::todas($usuario_id),
                'contar' => Notificacion::contarNoLeidas($usuario_id),
            ]);

        case 'notificaciones_marcar':
            requerirPost();
            if (!$usuario_id) jsonError('Debes iniciar sesión.', 401);
            $id = (int)($_POST['id'] ?? 0);
            $id ? Notificacion::marcarLeida($id) : Notificacion::marcarTodasLeidas($usuario_id);
            jsonSuccess(['message' => 'Notificaciones actualizadas.']);

        case 'chat_ia_enviar':
            requerirPost();
            if (!$usuario_id) jsonError('Debes iniciar sesión.', 401);
            $reporte_id = (int)($_POST['reporte_id'] ?? 0);
            $mensaje = trim($_POST['mensaje'] ?? '');
            if (!$reporte_id || $mensaje === '') jsonError('Reporte y mensaje requeridos.', 422);
            $conversacion = ChatIA::obtenerConversacion($usuario_id, $reporte_id);
            $conv_id = $conversacion ? $conversacion['id'] : ChatIA::crearConversacion($usuario_id, $reporte_id);
            ChatIA::guardarMensaje($conv_id, 'usuario', $mensaje);
            $respuesta = (new ChatIAService())->responder(Reporte::porId($reporte_id), ChatIA::historial($conv_id));
            ChatIA::guardarMensaje($conv_id, 'ia', $respuesta);
            jsonSuccess(['respuesta' => $respuesta, 'conversacion_id' => $conv_id]);

        case 'chat_ia_historial':
            if (!$usuario_id) jsonError('Debes iniciar sesión.', 401);
            $reporte_id = (int)($_GET['reporte_id'] ?? 0);
            if (!$reporte_id) jsonError('Reporte requerido.', 422);
            $conversacion = ChatIA::obtenerConversacion($usuario_id, $reporte_id);
            jsonSuccess($conversacion
                ? ['historial' => ChatIA::historial($conversacion['id']), 'conversacion_id' => $conversacion['id']]
                : ['historial' => []]);

        case 'clima_obtener':
            $distrito_id = (int)($_GET['distrito_id'] ?? 0);
            $lat = $_GET['lat'] ?? null;
            $lng = $_GET['lng'] ?? null;
            $servicio = new ClimaService();
            if ($distrito_id && ($distrito = Distrito::porId($distrito_id))) {
                $resultado = $servicio->obtenerPorDistrito($distrito['nombre']);
            } elseif ($lat !== null && $lng !== null) {
                $resultado = $servicio->obtenerPorCoordenadas($lat, $lng);
            } else {
                $resultado = $servicio->obtenerPorCoordenadas('-16.4090', '-71.5375');
            }
            jsonSuccess(['clima' => $resultado]);

        case 'mapa_reportes':
            $lat = $_GET['lat'] ?? '-16.4090';
            $lng = $_GET['lng'] ?? '-71.5375';
            jsonSuccess(['reportes' => Reporte::reportesCercanos($lat, $lng)]);

        case 'sistema_usuarios':
            if (!esRol('admin_sistema')) jsonError('No autorizado.', 403);
            jsonSuccess(['usuarios' => Usuario::listarTodos()]);

        case 'sistema_usuario_actualizar':
            requerirPost();
            if (!esRol('admin_sistema')) jsonError('No autorizado.', 403);
            $id = (int)($_POST['id'] ?? 0);
            if (!$id || !Usuario::porId($id)) jsonError('Usuario no encontrado.', 404);
            $datos = [];
            foreach (['nombre', 'correo', 'distrito_id'] as $campo) {
                if (isset($_POST[$campo])) $datos[$campo] = trim($_POST[$campo]);
            }
            if (isset($_POST['rol'])) {
                $roles = ['ciudadano', 'operador', 'admin_municipal', 'admin_sistema'];
                if (!in_array($_POST['rol'], $roles, true)) jsonError('Rol inválido.', 422);
                $datos['rol'] = $_POST['rol'];
            }
            if (!empty($_POST['password'])) $datos['password'] = $_POST['password'];
            if (!$datos) jsonError('No se recibieron cambios.', 422);
            Usuario::actualizar($id, $datos);
            jsonSuccess(['message' => 'Usuario actualizado.']);

        case 'sistema_usuario_estado':
            requerirPost();
            if (!esRol('admin_sistema')) jsonError('No autorizado.', 403);
            $id = (int)($_POST['id'] ?? 0);
            $accionEstado = $_POST['accion'] ?? '';
            if (!$id || !Usuario::porId($id)) jsonError('Usuario no encontrado.', 404);
            if ($id === (int)$usuario_id && in_array($accionEstado, ['suspender', 'eliminar'], true)) {
                jsonError('No puedes desactivar tu propia cuenta.', 409);
            }
            if ($accionEstado === 'suspender') Usuario::suspender($id);
            elseif ($accionEstado === 'activar') Usuario::activar($id);
            elseif ($accionEstado === 'eliminar') Usuario::eliminar($id);
            else jsonError('Acción inválida.', 422);
            jsonSuccess(['message' => 'Estado de usuario actualizado.']);

        case 'sistema_logs':
            if (!esRol('admin_sistema')) jsonError('No autorizado.', 403);
            $busqueda = trim($_GET['buscar'] ?? '');
            jsonSuccess(['logs' => ActividadLog::listar(200, $busqueda)]);

        case 'sistema_backups':
            if (!esRol('admin_sistema')) jsonError('No autorizado.', 403);
            jsonSuccess(['backups' => (new MantenimientoService())->listarBackups()]);

        case 'sistema_backup_generar':
            requerirPost();
            if (!esRol('admin_sistema')) jsonError('No autorizado.', 403);
            $backup = (new MantenimientoService())->generarBackup();
            ActividadLog::registrar($usuario_id, 'backup_mysql_generado', 'mantenimiento', 'ok', $backup['nombre']);
            jsonSuccess(['message' => 'Copia de seguridad generada.', 'backup' => $backup]);

        default:
            jsonError('Acción no válida: ' . $action, 404);
    }
} catch (Throwable $e) {
    error_log('[ReporteCiudadano] ' . $e->getMessage());
    jsonError('Ocurrió un error interno al procesar la solicitud.', 500);
}
