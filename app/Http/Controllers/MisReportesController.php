<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MisReportesController extends Controller
{
    public function __invoke(Request $request): View
    {
        $reportes = Reporte::with(['categoria', 'distrito'])
            ->withCount(['comentarios', 'likes', 'guardados'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('reportes.mine', compact('reportes'));
    }
}
