<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ClimaService
{
    public function obtener(?float $latitud = null, ?float $longitud = null, ?string $distrito = null): array
    {
        $apiKey = config('services.openweather.key');

        if ($apiKey && $latitud && $longitud) {
            try {
                $response = Http::timeout(8)->get('https://api.openweathermap.org/data/2.5/weather', [
                    'lat' => $latitud,
                    'lon' => $longitud,
                    'units' => 'metric',
                    'lang' => 'es',
                    'appid' => $apiKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    return [
                        'temperatura' => $data['main']['temp'] ?? null,
                        'condicion' => $data['weather'][0]['description'] ?? 'Sin descripcion',
                        'humedad' => $data['main']['humidity'] ?? null,
                        'fuente' => 'openweather',
                    ];
                }
            } catch (\Throwable) {
                // Se usa fallback local si la API externa no responde.
            }
        }

        return [
            'temperatura' => 18,
            'condicion' => 'Clima templado en Arequipa',
            'humedad' => 42,
            'distrito' => $distrito,
            'fuente' => 'simulado',
        ];
    }
}
