<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Services\ClimaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ClimaService $clima): View|RedirectResponse
    {
        $user = $request->user();

        $rolePanelRoute = match ($user->role) {
            'ciudadano' => 'reportes.index',
            'operador' => 'panel.operador',
            'admin_municipal' => 'panel.admin-municipal',
            'admin_sistema' => 'panel.admin-sistema',
            default => null,
        };

        if ($rolePanelRoute) {
            return redirect()->route($rolePanelRoute);
        }

        $stats = [
            'mis_reportes' => Reporte::where('user_id', $user->id)->count(),
            'pendientes' => Reporte::where('estado', 'pendiente')->count(),
            'en_proceso' => Reporte::where('estado', 'en_proceso')->count(),
            'resueltos' => Reporte::where('estado', 'resuelto')->count(),
        ];

        $reportes = Reporte::with(['categoria', 'distrito', 'user'])->latest()->take(6)->get();

        $panelRoute = 'panel.ciudadano';

        $climaActual = $clima->obtener(distrito: optional($user->district)->nombre);

        return view('dashboard', compact('stats', 'reportes', 'panelRoute', 'climaActual'));
    }
}
