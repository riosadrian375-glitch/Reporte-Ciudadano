<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use App\Models\Reporte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $misReportes = Reporte::where('user_id', $user->id)->withCount('likes')->get();

        return view('profile.show', [
            'user' => $user,
            'distritos' => Distrito::orderBy('nombre')->get(),
            'totalReportes' => $misReportes->count(),
            'totalLikes' => $misReportes->sum('likes_count'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'district_id' => ['nullable', 'exists:distritos,id'],
        ]);

        $request->user()->update($data);

        return back()->with('status', 'Perfil actualizado correctamente.');
    }
}
