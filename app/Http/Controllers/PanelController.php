<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Distrito;
use App\Models\Reporte;
use App\Models\User;
use App\Services\ClimaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function ciudadano(Request $request): View
    {
        $user = $request->user();

        $reportes = Reporte::with(['categoria', 'distrito'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(8);

        $stats = [
            'mis_reportes' => Reporte::where('user_id', $user->id)->count(),
            'pendientes' => Reporte::where('user_id', $user->id)->where('estado', 'pendiente')->count(),
            'en_proceso' => Reporte::where('user_id', $user->id)->where('estado', 'en_proceso')->count(),
            'resueltos' => Reporte::where('user_id', $user->id)->where('estado', 'resuelto')->count(),
        ];

        return view('panels.ciudadano', compact('reportes', 'stats'));
    }

    public function operador(Request $request, ClimaService $clima): View
    {
        $reportes = Reporte::with(['categoria', 'distrito', 'user'])
            ->where(function ($query) use ($request) {
                $query->where(function ($pendientes) {
                    $pendientes->where('estado', 'pendiente')
                        ->whereDoesntHave('asignaciones', fn ($asignacion) => $asignacion->where('estado', 'activa'));
                })->orWhere(function ($asignados) use ($request) {
                    $asignados->where('estado', 'en_proceso')
                        ->whereHas('asignaciones', fn ($asignacion) => $asignacion
                            ->where('operador_id', $request->user()->id)
                            ->where('estado', 'activa'));
                });
            })
            ->when($request->filled('categoria_id'), fn ($query) => $query->where('categoria_id', $request->categoria_id))
            ->when($request->filled('distrito_id'), fn ($query) => $query->where('distrito_id', $request->distrito_id))
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->estado))
            ->when($request->filled('buscar'), function ($query) use ($request) {
                $buscar = $request->buscar;

                $query->where(function ($inner) use ($buscar) {
                    $inner->where('titulo', 'like', "%{$buscar}%")
                        ->orWhere('descripcion', 'like', "%{$buscar}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('panels.operador', [
            'reportes' => $reportes,
            'categorias' => Categoria::orderBy('nombre')->get(),
            'distritos' => Distrito::orderBy('nombre')->get(),
            'pendientes' => Reporte::where('estado', 'pendiente')
                ->whereDoesntHave('asignaciones', fn ($query) => $query->where('estado', 'activa'))
                ->count(),
            'enProceso' => Reporte::where('estado', 'en_proceso')
                ->whereHas('asignaciones', fn ($query) => $query
                    ->where('operador_id', $request->user()->id)
                    ->where('estado', 'activa'))
                ->count(),
            'resueltos' => Reporte::where('estado', 'resuelto')->count(),
            'climaActual' => $clima->obtener(distrito: optional($request->user()->district)->nombre),
        ]);
    }

    public function adminMunicipal(ClimaService $clima): View
    {
        return view('panels.admin-municipal', [
            'totalReportes' => Reporte::count(),
            'pendientes' => Reporte::where('estado', 'pendiente')->count(),
            'urgentes' => Reporte::where('es_urgente', true)->count(),
            'operadores' => User::where('role', 'operador')->count(),
            'reportes' => Reporte::with(['categoria', 'distrito', 'user'])->latest()->take(12)->get(),
            'climaActual' => $clima->obtener(),
        ]);
    }

    public function adminSistema(): View
    {
        return view('panels.admin-sistema', [
            'usuarios' => User::latest()->take(15)->get(),
            'totales' => [
                'usuarios' => User::count(),
                'ciudadanos' => User::where('role', 'ciudadano')->count(),
                'operadores' => User::where('role', 'operador')->count(),
                'admins' => User::whereIn('role', ['admin_municipal', 'admin_sistema'])->count(),
            ],
        ]);
    }
}
