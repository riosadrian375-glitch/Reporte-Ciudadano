<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Notificacion;
use App\Models\Reporte;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AsignacionController extends Controller
{
    public function create(Reporte $reporte): View
    {
        return view('asignaciones.create', [
            'reporte' => $reporte->load(['categoria', 'distrito', 'user']),
            'operadores' => User::where('role', 'operador')->where('status', 'activo')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Reporte $reporte): RedirectResponse
    {
        $data = $request->validate([
            'operador_id' => ['required', 'exists:users,id'],
        ]);

        $asignacion = Asignacion::updateOrCreate(
            ['reporte_id' => $reporte->id, 'estado' => 'activa'],
            [
                'operador_id' => $data['operador_id'],
                'admin_id' => $request->user()->id,
            ]
        );

        $reporte->update(['estado' => 'en_proceso']);

        Notificacion::create([
            'user_id' => $asignacion->operador_id,
            'mensaje' => "Se te asigno el reporte: {$reporte->titulo}",
            'tipo' => 'asignacion',
            'referencia_id' => $reporte->id,
        ]);

        Notificacion::create([
            'user_id' => $reporte->user_id,
            'mensaje' => "Tu reporte paso a en proceso: {$reporte->titulo}",
            'tipo' => 'estado',
            'referencia_id' => $reporte->id,
        ]);

        return redirect()->route('panel.admin-municipal')->with('status', 'Reporte asignado correctamente.');
    }
}
