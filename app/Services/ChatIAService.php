<?php

namespace App\Services;

use App\Models\Reporte;

class ChatIAService
{
    public function responder(Reporte $reporte, string $mensaje): string
    {
        $texto = $this->normalizar($mensaje);

        if ($this->contiene($texto, ['de que trata', 'que es', 'proyecto', 'reporte ciudadano', 'sistema'])) {
            return $this->respuestaProyecto();
        }

        if ($this->contiene($texto, ['como usar', 'como funciona', 'pasos', 'registrar', 'crear reporte', 'publicar'])) {
            return $this->respuestaUso();
        }

        if ($this->contiene($texto, ['estado', 'avance', 'seguimiento', 'proceso', 'resuelto', 'pendiente'])) {
            return $this->respuestaEstado($reporte);
        }

        if ($this->contiene($texto, ['urgente', 'emergencia', 'peligro', 'riesgo', 'accidente', 'robo', 'incendio'])) {
            return $this->respuestaEmergencia($reporte);
        }

        if ($this->contiene($texto, ['foto', 'imagen', 'video', 'evidencia', 'archivo', 'prueba'])) {
            return $this->respuestaEvidencias();
        }

        if ($this->contiene($texto, ['municipalidad', 'operador', 'admin', 'administrador', 'asignar', 'atiende'])) {
            return $this->respuestaRoles();
        }

        if ($this->contiene($texto, ['comentario', 'comentar', 'aportar', 'informacion adicional'])) {
            return $this->respuestaComentarios();
        }

        if ($this->fueraDeSoporte($texto)) {
            return 'Puedo ayudarte con el uso de Reporte Ciudadano, seguimiento de reportes, estados, evidencias, roles y recomendaciones de soporte. Para otros temas, te sugiero consultar una fuente especializada.';
        }

        return $this->respuestaReporte($reporte);
    }

    private function respuestaProyecto(): string
    {
        return 'Reporte Ciudadano es una plataforma web para que los vecinos registren incidencias urbanas de Arequipa, como baches, limpieza publica, seguridad, alumbrado, areas verdes o transito. El objetivo es ordenar los reportes, permitir su seguimiento y facilitar que la municipalidad los revise, asigne y cierre con evidencia.';
    }

    private function respuestaUso(): string
    {
        return 'Para usar el sistema: 1) inicia sesion o crea una cuenta desde la pantalla principal; 2) registra un reporte con categoria, distrito, descripcion, ubicacion y evidencias; 3) revisa el estado desde Mis reportes o el panel; 4) agrega comentarios si necesitas ampliar informacion; 5) espera la revision municipal y el cierre con evidencia cuando corresponda.';
    }

    private function respuestaEstado(Reporte $reporte): string
    {
        $estado = str_replace('_', ' ', $reporte->estado);
        $moderacion = str_replace('_', ' ', $reporte->estado_moderacion);

        return "Este reporte esta en estado '{$estado}' y su moderacion figura como '{$moderacion}'. Si esta pendiente, aun debe ser revisado; si esta en proceso, ya fue tomado para atencion; si esta resuelto, deberia contar con evidencia o comentario de cierre.";
    }

    private function respuestaEmergencia(Reporte $reporte): string
    {
        $prioridad = $reporte->es_urgente
            ? 'Este reporte esta marcado como urgente, por lo que debe priorizarse en el panel municipal.'
            : 'Este reporte no esta marcado como urgente, pero puedes aportar mas informacion si el riesgo aumento.';

        return $prioridad . ' Si existe peligro inmediato para personas, llama primero a los canales de emergencia correspondientes y luego usa la plataforma para dejar constancia con ubicacion y evidencias.';
    }

    private function respuestaEvidencias(): string
    {
        return 'Las evidencias ayudan a que el reporte sea mas claro. Puedes adjuntar imagenes JPG, PNG o WEBP y videos cortos. Para un buen reporte, toma fotos donde se vea el problema, la referencia de ubicacion y, si es posible, la magnitud del riesgo.';
    }

    private function respuestaRoles(): string
    {
        return 'El ciudadano registra y sigue reportes. El administrador municipal revisa reportes y puede asignarlos. El operador atiende incidencias asignadas y registra evidencia de cierre. El administrador del sistema gestiona usuarios y supervision general.';
    }

    private function respuestaComentarios(): string
    {
        return 'Los comentarios sirven para complementar el reporte sin crear otro caso. Agrega datos utiles como hora del incidente, referencias cercanas, cambios en el riesgo o detalles que ayuden al operador municipal a ubicar mejor el problema.';
    }

    private function respuestaReporte(Reporte $reporte): string
    {
        $urgencia = $reporte->es_urgente ? ' Esta marcado como urgente.' : '';

        return "Puedo orientarte sobre este reporte: '{$reporte->titulo}'. Pertenece a la categoria {$reporte->categoria->nombre}, distrito {$reporte->distrito->nombre}, y actualmente esta en estado {$reporte->estado}.{$urgencia} Puedes usar comentarios para ampliar datos, Chat IA para orientacion y el seguimiento para revisar avances.";
    }

    private function contiene(string $texto, array $palabras): bool
    {
        foreach ($palabras as $palabra) {
            if (str_contains($texto, $palabra)) {
                return true;
            }
        }

        return false;
    }

    private function fueraDeSoporte(string $texto): bool
    {
        return $this->contiene($texto, [
            'matematica',
            'programa en',
            'codigo de',
            'historia universal',
            'receta',
            'apuesta',
            'hack',
            'contrasena de otro',
        ]);
    }

    private function normalizar(string $mensaje): string
    {
        $texto = mb_strtolower(trim($mensaje));
        $reemplazos = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ñ' => 'n',
        ];

        return strtr($texto, $reemplazos);
    }
}
