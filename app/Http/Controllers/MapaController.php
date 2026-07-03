<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MapaController extends Controller
{
    public function index(): View
    {
        return view('mapa.index');
    }

    public function data(): JsonResponse
    {
        $reportes = Reporte::with(['categoria', 'distrito'])
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->latest()
            ->get()
            ->map(fn (Reporte $reporte) => [
                'id' => $reporte->id,
                'titulo' => $reporte->titulo,
                'categoria' => $reporte->categoria->nombre,
                'distrito' => $reporte->distrito->nombre,
                'estado' => $reporte->estado,
                'latitud' => (float) $reporte->latitud,
                'longitud' => (float) $reporte->longitud,
                'url' => route('reportes.show', $reporte),
            ]);

        return response()->json($reportes);
    }
}
