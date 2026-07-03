<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuardadoController extends Controller
{
    public function index(Request $request): View
    {
        $reportes = Reporte::with(['categoria', 'distrito', 'user'])
            ->withCount(['comentarios', 'likes', 'guardados'])
            ->whereHas('guardados', fn ($query) => $query->where('user_id', $request->user()->id))
            ->latest()
            ->paginate(10);

        return view('guardados.index', compact('reportes'));
    }
}
