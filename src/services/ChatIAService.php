<?php

class ChatIAService {

    private $apiToken;
    private $modelo;

    public function __construct() {
        $this->apiToken = defined('HUGGINGFACE_API_TOKEN') ? HUGGINGFACE_API_TOKEN : '';
        $this->modelo = defined('HUGGINGFACE_CHAT_MODEL') ? HUGGINGFACE_CHAT_MODEL : 'microsoft/DialoGPT-medium';
    }

    public function responder($reporte, $historial) {
        if (empty($this->apiToken)) {
            return $this->respuestaSimulada($reporte, $historial);
        }

        $contexto = $this->construirContexto($reporte, $historial);

        $url = "https://api-inference.huggingface.co/models/{$this->modelo}";

        $payload = json_encode(['inputs' => $contexto]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiToken,
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $respuesta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $respuesta) {
            $resultados = json_decode($respuesta, true);
            if ($resultados && isset($resultados[0]['generated_text'])) {
                $textoGenerado = $resultados[0]['generated_text'];
                $respuestaLimpia = $this->limpiarRespuesta($textoGenerado, $contexto);
                if (!empty($respuestaLimpia)) {
                    return $respuestaLimpia;
                }
            }
        }

        if ($httpCode === 503) {
            return $this->respuestaCuandoModeloCargando();
        }

        return $this->respuestaSimulada($reporte, $historial);
    }

    private function construirContexto($reporte, $historial) {
        $ultimoMensaje = '';
        foreach (array_reverse($historial) as $msg) {
            if ($msg['remitente'] === 'usuario') {
                $ultimoMensaje = $msg['mensaje'];
                break;
            }
        }

        $contexto = "Eres un asistente de la plataforma ReporteCiudadano de Arequipa, Peru. ";
        $contexto .= "Contexto del reporte:\n";
        $contexto .= "- Titulo: {$reporte['titulo']}\n";
        $contexto .= "- Categoria: {$reporte['categoria_nombre']}\n";
        $contexto .= "- Distrito: {$reporte['distrito_nombre']}\n";
        $contexto .= "- Descripcion: {$reporte['descripcion']}\n\n";
        $contexto .= "Responde de forma util, amable y con consejos practicos. ";
        $contexto .= "Si te preguntan sobre procedimientos municipales en Arequipa, orienta al ciudadano sobre que pasos seguir. ";
        $contexto .= "Usuario pregunta: {$ultimoMensaje}\n";
        $contexto .= "Asistente:";

        return $contexto;
    }

    private function limpiarRespuesta($textoGenerado, $contexto) {
        if (strpos($textoGenerado, $contexto) === 0) {
            $textoGenerado = substr($textoGenerado, strlen($contexto));
        }
        $textoGenerado = trim($textoGenerado);
        $lineas = explode("\n", $textoGenerado);
        $textoGenerado = trim($lineas[0]);

        if (empty($textoGenerado) || strlen($textoGenerado) < 5) {
            return '';
        }

        return $textoGenerado;
    }

    private function respuestaCuandoModeloCargando() {
        $respuestas = [
            "El modelo de IA esta cargando en los servidores de Hugging Face. Esto puede tomar hasta 2 minutos en la primera solicitud. Por favor, intenta de nuevo en unos momentos.",
            "El modelo conversacional se esta inicializando. Los modelos gratuitos de Hugging Face pueden tardar en cargar si no han sido usados recientemente. Intenta nuevamente.",
        ];
        return $respuestas[array_rand($respuestas)];
    }

    private function respuestaSimulada($reporte, $historial) {
        $ultimoMensaje = '';
        foreach (array_reverse($historial) as $msg) {
            if ($msg['remitente'] === 'usuario') {
                $ultimoMensaje = strtolower($msg['mensaje']);
                break;
            }
        }

        $categoria = strtolower($reporte['categoria_nombre'] ?? '');

        if (strpos($ultimoMensaje, 'carro') !== false || strpos($ultimoMensaje, 'auto') !== false || strpos($ultimoMensaje, 'vehiculo') !== false) {
            if (strpos($categoria, 'hueco') !== false) {
                return "Si tu vehiculo resulto danado por un hueco en la via, te recomiendo:\n\n1. Documenta todo: toma fotos del hueco, del dano y de la ubicacion exacta.\n2. Presenta un reclamo formal en la Municipalidad Provincial de Arequipa (oficina de reclamos o mesa de partes).\n3. Adjunta tu reporte de ReporteCiudadano como evidencia.\n4. Si tienes seguro vehicular, contacta a tu aseguradora.\n5. Puedes solicitar una copia del reporte para respaldar tu reclamo.\n\nEn Arequipa, la Gerencia de Transportes puede darte orientacion especifica sobre el proceso.";
            }
        }

        if (strpos($ultimoMensaje, 'como') !== false && strpos($ultimoMensaje, 'report') !== false) {
            return "Para hacer un reporte en ReporteCiudadano:\n\n1. Inicia sesion en tu cuenta.\n2. Haz clic en \"Nuevo Reporte\".\n3. Selecciona la categoria correcta (Huecos, Basura, Alumbrado, etc.).\n4. Marca la ubicacion exacta en el mapa.\n5. Agrega una descripcion clara y, si es posible, fotos.\n6. Opcionalmente marca como urgente si la situacion lo requiere.\n\nTu reporte sera moderado y publicado en el feed para que otros ciudadanos puedan apoyarlo.";
        }

        if (strpos($ultimoMensaje, 'emergencia') !== false || strpos($ultimoMensaje, 'emergencia') !== false || strpos($ultimoMensaje, 'peligro') !== false) {
            return "Si es una emergencia, contacta inmediatamente:\n\n- POLICIA: 105\n- BOMBEROS: 116\n- SAMU (ambulancia): 106\n- SERENAZGO: 054-201234\n- CENTRAL DE EMERGENCIAS: 911\n\nEn ReporteCiudadano puedes crear un reporte de emergencia marcandolo como urgente. Si es algo que requiere atencion inmediata, llama directamente a los numeros de emergencia.";
        }

        $respuestas = [
            "Gracias por tu consulta sobre este reporte en {$reporte['distrito_nombre']}. La categoria '{$reporte['categoria_nombre']}' es gestionada por la Municipalidad de Arequipa. Te recomiendo hacer seguimiento periodico de tu reporte y animar a otros vecinos a que tambien lo apoyen con likes para mayor visibilidad.",
            "Entiendo tu preocupacion. En Arequipa, los reportes de tipo '{$reporte['categoria_nombre']}' son atendidos segun su prioridad. Los reportes con mas apoyo de la comunidad suelen recibir atencion mas rapida. Comparte tu reporte en redes sociales para difundirlo.",
            "Buena pregunta. Para este tipo de situacion en {$reporte['distrito_nombre']}, puedes comunicarte tambien con la municipalidad distrital directamente. Los numeros de contacto de cada municipio estan disponibles en sus sitios web oficiales.",
            "El estado actual de este reporte es '{$reporte['estado']}'. Si el estado no cambia en varias semanas, puedes crear un nuevo reporte con referencia al anterior o contactar a la municipalidad para solicitar actualizacion.",
            "Ademas de ReporteCiudadano, te sugiero mantener un registro personal de tus reportes con fechas y numeros de seguimiento. Esto es util si necesitas escalar el problema a instancias superiores como la Defensoria del Pueblo o la Contraloria.",
        ];

        return $respuestas[array_rand($respuestas)] .
            "\n\n*Nota: Respuesta generada sin conexion a IA. " .
            "Configura HUGGINGFACE_API_TOKEN en config/api_keys.php para obtener respuestas con IA real. " .
            "Registrate gratis en https://huggingface.co/ y genera un token en https://huggingface.co/settings/tokens";
    }
}
