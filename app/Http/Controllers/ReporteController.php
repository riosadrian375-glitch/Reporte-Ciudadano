<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Distrito;
use App\Models\Reporte;
use App\Services\ClimaService;
use App\Services\ModeracionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function index(Request $request, ClimaService $clima): View
    {
        $reportes = Reporte::with(['categoria', 'distrito', 'user'])
            ->withCount(['comentarios', 'likes', 'guardados'])
            ->when(request('categoria_id'), fn ($query, $categoriaId) => $query->where('categoria_id', $categoriaId))
            ->when(request('distrito_id'), fn ($query, $distritoId) => $query->where('distrito_id', $distritoId))
            ->when(request('estado'), fn ($query, $estado) => $query->where('estado', $estado))
            ->when(request('buscar'), function ($query, $buscar) {
                $query->where(function ($inner) use ($buscar) {
                    $inner->where('titulo', 'like', "%{$buscar}%")
                        ->orWhere('descripcion', 'like', "%{$buscar}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('reportes.index', [
            'reportes' => $reportes,
            'categorias' => Categoria::orderBy('nombre')->get(),
            'distritos' => Distrito::orderBy('nombre')->get(),
            'climaActual' => $clima->obtener(distrito: optional($request->user()?->district)->nombre),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->role === 'ciudadano', 403);

        return view('reportes.create', [
            'categorias' => Categoria::orderBy('nombre')->get(),
            'distritos' => Distrito::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request, ModeracionService $moderacion, ClimaService $clima): RedirectResponse
    {
        abort_unless($request->user()->role === 'ciudadano', 403);

        $data = $request->validate([
            'categoria_id' => ['required', 'exists:categorias,id'],
            'distrito_id' => ['required', 'exists:distritos,id'],
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['required', 'string', 'min:10'],
            'direccion' => ['nullable', 'string', 'max:300'],
            'latitud' => ['nullable', 'numeric'],
            'longitud' => ['nullable', 'numeric'],
            'es_urgente' => ['nullable', 'boolean'],
            'imagenes.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'videos.*' => ['nullable', 'file', 'mimes:mp4,mov,webm', 'max:51200'],
            'video_durations' => ['nullable', 'array'],
            'video_durations.*' => ['nullable', 'numeric', 'max:60'],
        ]);

        foreach ($request->file('videos', []) as $index => $video) {
            $duration = (float) $request->input("video_durations.{$index}", 0);

            if ($duration <= 0 || $duration > 60) {
                return back()
                    ->withErrors(["videos.{$index}" => 'Cada video debe durar como máximo 1 minuto.'])
                    ->withInput();
            }
        }

        $resultadoModeracion = $moderacion->analizar($data['titulo'] . ' ' . $data['descripcion']);
        $distrito = Distrito::find($data['distrito_id']);

        $reporte = Reporte::create([
            ...$data,
            'user_id' => $request->user()->id,
            'es_urgente' => $request->boolean('es_urgente'),
            'estado_moderacion' => $resultadoModeracion['estado'],
            'moderado' => $resultadoModeracion['moderado'],
            'clima_momento' => $clima->obtener(
                isset($data['latitud']) ? (float) $data['latitud'] : null,
                isset($data['longitud']) ? (float) $data['longitud'] : null,
                $distrito?->nombre
            ),
        ]);

        foreach ($request->file('imagenes', []) as $imagen) {
            $reporte->imagenes()->create([
                'ruta_archivo' => $imagen->store('reportes', 'public'),
            ]);
        }

        foreach ($request->file('videos', []) as $index => $video) {
            $reporte->videos()->create([
                'ruta_archivo' => $video->store('reportes/videos', 'public'),
                'duracion_segundos' => (int) round((float) $request->input("video_durations.{$index}", 0)) ?: null,
            ]);
        }

        return redirect()->route('reportes.show', $reporte)->with('status', 'Reporte registrado correctamente.');
    }

    public function show(Reporte $reporte): View
    {
        $reporte->load(['categoria', 'distrito', 'user', 'comentarios.user', 'imagenes', 'videos', 'evidencias.operador'])
            ->loadCount(['comentarios', 'likes', 'guardados']);

        return view('reportes.show', compact('reporte'));
    }
}
