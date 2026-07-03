<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\Reporte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReporteEstadoController extends Controller
{
    public function update(Request $request, Reporte $reporte): RedirectResponse
    {
        $data = $request->validate([
            'estado' => ['required', 'in:pendiente,en_proceso,resuelto,rechazado'],
        ]);

        $reporte->update(['estado' => $data['estado']]);

        Notificacion::create([
            'user_id' => $reporte->user_id,
            'mensaje' => "Tu reporte cambio de estado a {$data['estado']}: {$reporte->titulo}",
            'tipo' => 'estado',
            'referencia_id' => $reporte->id,
        ]);

        return back()->with('status', 'Estado actualizado.');
    }
}
