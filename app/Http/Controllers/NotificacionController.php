<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    public function index(Request $request): View
    {
        $notificaciones = $request->user()
            ->notificaciones()
            ->latest()
            ->paginate(15);

        return view('notificaciones.index', compact('notificaciones'));
    }

    public function markAll(Request $request): RedirectResponse
    {
        $request->user()->notificaciones()->update(['leida' => true]);

        return back()->with('status', 'Notificaciones marcadas como leidas.');
    }
}
