<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\Reporte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvidenciaController extends Controller
{
    public function create(Reporte $reporte): View
    {
        return view('evidencias.create', compact('reporte'));
    }

    public function store(Request $request, Reporte $reporte): RedirectResponse
    {
        $data = $request->validate([
            'comentario_resolucion' => ['required', 'string', 'min:10'],
            'archivo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $archivo = $request->file('archivo');
        $path = $archivo->store('evidencias', 'public');

        $reporte->evidencias()->create([
            'operador_id' => $request->user()->id,
            'comentario_resolucion' => $data['comentario_resolucion'],
            'ruta_archivo' => $path,
            'tipo_mime' => $archivo->getMimeType() ?: 'application/octet-stream',
        ]);

        $reporte->update(['estado' => 'resuelto']);

        Notificacion::create([
            'user_id' => $reporte->user_id,
            'mensaje' => "Tu reporte fue marcado como resuelto: {$reporte->titulo}",
            'tipo' => 'estado',
            'referencia_id' => $reporte->id,
        ]);

        return redirect()->route('reportes.show', $reporte)->with('status', 'Evidencia registrada y reporte resuelto.');
    }
}
