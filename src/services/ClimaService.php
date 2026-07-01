<?php

class ClimaService {

    private $apiKey;
    private $baseUrl = 'https://api.openweathermap.org/data/2.5/weather';

    public function __construct() {
        $this->apiKey = defined('OPENWEATHER_API_KEY') ? OPENWEATHER_API_KEY : '';
    }

    public function obtenerPorCoordenadas($lat, $lng) {
        if (empty($this->apiKey)) {
            return $this->respuestaSimulada();
        }

        $url = $this->baseUrl . "?lat={$lat}&lon={$lng}&units=metric&lang=es&appid={$this->apiKey}";

        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'method' => 'GET',
                'header' => 'Accept: application/json'
            ]
        ]);

        $respuesta = @file_get_contents($url, false, $context);

        if ($respuesta === false) {
            return $this->respuestaSimulada();
        }

        $datos = json_decode($respuesta, true);

        if (!$datos || !isset($datos['main'])) {
            return $this->respuestaSimulada();
        }

        return [
            'success' => true,
            'temperatura' => round($datos['main']['temp']),
            'sensacion_termica' => round($datos['main']['feels_like']),
            'humedad' => $datos['main']['humidity'],
            'descripcion' => $datos['weather'][0]['description'],
            'icono' => $datos['weather'][0]['icon'],
            'ciudad' => $datos['name'] ?? 'Arequipa',
            'viento' => isset($datos['wind']['speed']) ? round($datos['wind']['speed'] * 3.6, 1) : 0,
            'presion' => $datos['main']['pressure'] ?? 0,
        ];
    }

    public function obtenerPorDistrito($distrito) {
        $coords = $this->coordenadasDistrito($distrito);
        if ($coords) {
            return $this->obtenerPorCoordenadas($coords['lat'], $coords['lng']);
        }
        return $this->obtenerPorCoordenadas('-16.4090', '-71.5375');
    }

    private function coordenadasDistrito($distrito) {
        $mapa = [
            'Cercado' => ['lat' => '-16.4090', 'lng' => '-71.5375'],
            'Miraflores' => ['lat' => '-16.4110', 'lng' => '-71.5280'],
            'Cayma' => ['lat' => '-16.3780', 'lng' => '-71.5430'],
            'Cerro Colorado' => ['lat' => '-16.3850', 'lng' => '-71.5600'],
            'Yanahuara' => ['lat' => '-16.3950', 'lng' => '-71.5200'],
            'Paucarpata' => ['lat' => '-16.4300', 'lng' => '-71.5100'],
            'Jose Luis Bustamante y Rivero' => ['lat' => '-16.4200', 'lng' => '-71.5300'],
            'Mariano Melgar' => ['lat' => '-16.4150', 'lng' => '-71.5150'],
            'Alto Selva Alegre' => ['lat' => '-16.4000', 'lng' => '-71.5050'],
            'Sachaca' => ['lat' => '-16.3800', 'lng' => '-71.5550'],
            'Tiabaya' => ['lat' => '-16.4500', 'lng' => '-71.5900'],
            'Hunter' => ['lat' => '-16.4400', 'lng' => '-71.5700'],
            'Socabaya' => ['lat' => '-16.4600', 'lng' => '-71.5400'],
            'Characato' => ['lat' => '-16.4800', 'lng' => '-71.5000'],
            'Sabandia' => ['lat' => '-16.4550', 'lng' => '-71.5200'],
        ];
        $normalizado = strtolower(trim($distrito));
        foreach ($mapa as $nombre => $coord) {
            if (strtolower($nombre) === $normalizado) {
                return $coord;
            }
        }
        return null;
    }

    private function respuestaSimulada() {
        $condiciones = ['Despejado', 'Nublado', 'Lluvia ligera', 'Parcialmente nublado', 'Cielo claro'];
        return [
            'success' => false,
            'simulado' => true,
            'temperatura' => rand(14, 24),
            'sensacion_termica' => rand(12, 22),
            'humedad' => rand(30, 80),
            'descripcion' => $condiciones[array_rand($condiciones)],
            'icono' => '01d',
            'ciudad' => 'Arequipa',
            'viento' => rand(0, 15),
            'presion' => rand(1010, 1025),
            'nota' => 'Datos simulados. Configura OPENWEATHER_API_KEY en config/api_keys.php para datos reales. Registrate gratis en https://home.openweathermap.org/users/sign_up'
        ];
    }
}
