<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InteraccionReporteController extends Controller
{
    public function toggleLike(Request $request, Reporte $reporte): RedirectResponse
    {
        $like = $reporte->likes()->where('user_id', $request->user()->id)->first();

        if ($like) {
            $like->delete();
            return back();
        }

        $reporte->likes()->create(['user_id' => $request->user()->id]);

        return back();
    }

    public function toggleGuardado(Request $request, Reporte $reporte): RedirectResponse
    {
        $guardado = $reporte->guardados()->where('user_id', $request->user()->id)->first();

        if ($guardado) {
            $guardado->delete();
            return back()->with('status', 'Reporte quitado de guardados.');
        }

        $reporte->guardados()->create(['user_id' => $request->user()->id]);

        return back()->with('status', 'Reporte guardado.');
    }
}
