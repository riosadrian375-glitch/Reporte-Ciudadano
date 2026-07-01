<?php

class ModeracionService {

    private $apiToken;
    private $modelo;

    public function __construct() {
        $this->apiToken = defined('HUGGINGFACE_API_TOKEN') ? HUGGINGFACE_API_TOKEN : '';
        $this->modelo = defined('HUGGINGFACE_MODERACION_MODEL') ? HUGGINGFACE_MODERACION_MODEL : 'unitary/toxic-bert';
    }

    public function moderarTexto($texto) {
        if (empty($this->apiToken)) {
            return $this->moderacionSimulada($texto);
        }

        $url = "https://api-inference.huggingface.co/models/{$this->modelo}";

        $payload = json_encode(['inputs' => $texto]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiToken,
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);

        $respuesta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $respuesta === false) {
            return $this->moderacionSimulada($texto);
        }

        $resultados = json_decode($respuesta, true);

        if (!$resultados || !is_array($resultados)) {
            return $this->moderacionSimulada($texto);
        }

        return $this->analizarResultadosHF($resultados);
    }

    private function analizarResultadosHF($resultados) {
        $umbral_toxico = 0.7;

        if (isset($resultados[0]) && is_array($resultados[0])) {
            foreach ($resultados[0] as $prediccion) {
                $label = $prediccion['label'] ?? '';
                $score = $prediccion['score'] ?? 0;

                $palabras_toxicas = ['toxic', 'obscene', 'insult', 'threat', 'identity_hate', 'severe_toxic'];
                foreach ($palabras_toxicas as $palabra) {
                    if (stripos($label, $palabra) !== false && $score > $umbral_toxico) {
                        return [
                            'aprobado' => false,
                            'estado' => 'rechazado',
                            'confianza' => $score,
                            'razon' => 'Contenido potencialmente ofensivo detectado'
                        ];
                    }
                }
            }
        }

        return [
            'aprobado' => true,
            'estado' => 'aprobado',
            'confianza' => 1.0,
            'razon' => 'Contenido aprobado'
        ];
    }

    private function moderacionSimulada($texto) {
        $palabras_prohibidas = ['spam', 'xxx', 'violencia', 'odio', 'discriminacion'];
        $textoLower = strtolower($texto);

        foreach ($palabras_prohibidas as $palabra) {
            if (strpos($textoLower, $palabra) !== false) {
                return [
                    'aprobado' => false,
                    'estado' => 'rechazado',
                    'confianza' => 0.95,
                    'razon' => 'Contenido no permitido detectado',
                    'nota' => 'Moderacion local simplificada. Configura HUGGINGFACE_API_TOKEN para moderacion con IA real.'
                ];
            }
        }

        return [
            'aprobado' => true,
            'estado' => 'aprobado',
            'confianza' => 0.98,
            'razon' => 'Contenido aprobado automaticamente',
            'nota' => 'Moderacion local simplificada. Configura HUGGINGFACE_API_TOKEN para moderacion con IA real.'
        ];
    }

    /*
     * NOTA SOBRE MODERACION VISUAL (imagenes/video):
     *
     * La moderacion de contenido visual con IA no se ha implementado en este
     * boceto inicial. Las funciones para ello estan definidas pero usan una
     * validacion simplificada (solo formato y tamano de archivo).
     *
     * Para una iteracion futura, se recomienda:
     * - Usar modelos de Hugging Face como "google/vit-base-patch16-224" para
     *   clasificacion de imagenes, o
     * - Usar la API de Sightengine (tiene plan gratuito limitado) para moderacion
     *   de imagenes, o
     * - Usar AWS Rekognition / Google Cloud Vision (requieren tarjeta de credito)
     *
     * Por ahora, la validacion se limita a:
     * - Tamano maximo de imagen: 5MB
     * - Formatos permitidos: JPEG, PNG, WebP
     * - Tamano maximo de video: 50MB
     * - Formatos de video: MP4, WebM
     */
    public function validarArchivo($archivo) {
        $errores = [];

        $tipo = $archivo['type'] ?? '';
        $tamano = $archivo['size'] ?? 0;
        $error = $archivo['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error !== UPLOAD_ERR_OK) {
            return ['valido' => false, 'errores' => ['Error al subir el archivo']];
        }

        $esImagen = in_array($tipo, ['image/jpeg', 'image/png', 'image/webp']);
        $esVideo = in_array($tipo, ['video/mp4', 'video/webm']);

        if (!$esImagen && !$esVideo) {
            return ['valido' => false, 'errores' => ['Formato de archivo no soportado']];
        }

        if ($esImagen && $tamano > 5 * 1024 * 1024) {
            return ['valido' => false, 'errores' => ['La imagen no debe superar los 5MB']];
        }

        if ($esVideo && $tamano > 50 * 1024 * 1024) {
            return ['valido' => false, 'errores' => ['El video no debe superar los 50MB']];
        }

        return ['valido' => true, 'errores' => []];
    }
}
