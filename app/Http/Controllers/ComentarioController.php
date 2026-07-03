<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ComentarioController extends Controller
{
    public function store(Request $request, Reporte $reporte): RedirectResponse
    {
        $data = $request->validate([
            'contenido' => ['required', 'string', 'min:2', 'max:1000'],
        ]);

        $reporte->comentarios()->create([
            'user_id' => $request->user()->id,
            'contenido' => $data['contenido'],
            'estado_moderacion' => 'pendiente',
        ]);

        return back()->with('status', 'Comentario agregado correctamente.');
    }
}
