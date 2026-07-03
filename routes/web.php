<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmergenciaController;
use App\Http\Controllers\EvidenciaController;
use App\Http\Controllers\InteraccionReporteController;
use App\Http\Controllers\MapaController;
use App\Http\Controllers\GuardadoController;
use App\Http\Controllers\MisReportesController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ReporteEstadoController;
use App\Http\Controllers\UserAdminController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/index.php', function () {
    $id = request('id');

    return match (request('view')) {
        'login' => redirect()->route('login'),
        'register' => redirect()->route('register'),
        'feed' => redirect()->route('reportes.index'),
        'reporte/crear' => redirect()->route('reportes.create'),
        'reporte/detalle' => $id ? redirect()->route('reportes.show', $id) : redirect()->route('reportes.index'),
        'perfil' => redirect()->route('profile.show'),
        'mis-reportes' => redirect()->route('reportes.mine'),
        'guardados' => redirect()->route('guardados.index'),
        'notificaciones' => redirect()->route('notificaciones.index'),
        'emergencias' => redirect()->route('emergencias.index'),
        'mapa' => redirect()->route('mapa.index'),
        'ciudadano_dashboard' => redirect()->route('panel.ciudadano'),
        'operador_dashboard' => redirect()->route('panel.operador'),
        'admin_dashboard' => redirect()->route('panel.admin-municipal'),
        'sistema_dashboard' => redirect()->route('panel.admin-sistema'),
        default => redirect()->route('home'),
    };
});

Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => redirect()->route('home'))->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/registro', fn () => redirect()->route('home'))->name('register');
    Route::post('/registro', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/perfil', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/mis-reportes', MisReportesController::class)->name('reportes.mine');
    Route::get('/guardados', [GuardadoController::class, 'index'])->name('guardados.index');
    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/leidas', [NotificacionController::class, 'markAll'])->name('notificaciones.mark-all');
    Route::get('/emergencias', [EmergenciaController::class, 'index'])->name('emergencias.index');
    Route::get('/mapa', [MapaController::class, 'index'])->name('mapa.index');
    Route::get('/api/reportes-mapa', [MapaController::class, 'data'])->name('api.reportes-mapa');
    Route::get('/panel/ciudadano', [PanelController::class, 'ciudadano'])
        ->middleware('role:ciudadano,admin_sistema')
        ->name('panel.ciudadano');
    Route::get('/panel/operador', [PanelController::class, 'operador'])
        ->middleware('role:operador,admin_sistema')
        ->name('panel.operador');
    Route::get('/panel/admin-municipal', [PanelController::class, 'adminMunicipal'])
        ->middleware('role:admin_municipal,admin_sistema')
        ->name('panel.admin-municipal');
    Route::get('/panel/admin-sistema', [PanelController::class, 'adminSistema'])
        ->middleware('role:admin_sistema')
        ->name('panel.admin-sistema');
    Route::patch('/usuarios/{user}', [UserAdminController::class, 'update'])
        ->middleware('role:admin_sistema')
        ->name('usuarios.update');
    Route::post('/reportes/{reporte}/comentarios', [ComentarioController::class, 'store'])->name('reportes.comentarios.store');
    Route::get('/reportes/{reporte}/chat', [ChatController::class, 'show'])->name('reportes.chat.show');
    Route::post('/reportes/{reporte}/chat', [ChatController::class, 'ask'])->name('reportes.chat.ask');
    Route::post('/reportes/{reporte}/like', [InteraccionReporteController::class, 'toggleLike'])->name('reportes.like');
    Route::post('/reportes/{reporte}/guardar', [InteraccionReporteController::class, 'toggleGuardado'])->name('reportes.guardar');
    Route::get('/reportes/{reporte}/asignar', [AsignacionController::class, 'create'])
        ->middleware('role:admin_municipal,admin_sistema')
        ->name('reportes.asignar.create');
    Route::post('/reportes/{reporte}/asignar', [AsignacionController::class, 'store'])
        ->middleware('role:admin_municipal,admin_sistema')
        ->name('reportes.asignar.store');
    Route::patch('/reportes/{reporte}/estado', [ReporteEstadoController::class, 'update'])
        ->middleware('role:operador,admin_municipal,admin_sistema')
        ->name('reportes.estado.update');
    Route::get('/reportes/{reporte}/evidencias/create', [EvidenciaController::class, 'create'])
        ->middleware('role:operador,admin_municipal,admin_sistema')
        ->name('reportes.evidencias.create');
    Route::post('/reportes/{reporte}/evidencias', [EvidenciaController::class, 'store'])
        ->middleware('role:operador,admin_municipal,admin_sistema')
        ->name('reportes.evidencias.store');
    Route::resource('reportes', ReporteController::class)->only(['index', 'create', 'store', 'show']);
});
