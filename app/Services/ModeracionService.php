<?php

namespace App\Services;

class ModeracionService
{
    private array $bloqueadas = [
        'insulto',
        'amenaza',
        'odio',
        'violencia extrema',
        'spam',
    ];

    public function analizar(string $texto): array
    {
        $normalizado = mb_strtolower($texto);

        foreach ($this->bloqueadas as $palabra) {
            if (str_contains($normalizado, $palabra)) {
                return [
                    'estado' => 'en_revision',
                    'moderado' => false,
                    'motivo' => "Contenido enviado a revision por posible coincidencia: {$palabra}.",
                ];
            }
        }

        return [
            'estado' => 'aprobado',
            'moderado' => true,
            'motivo' => 'Moderacion local aprobada.',
        ];
    }
}
