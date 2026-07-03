<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:ciudadano,admin_municipal,operador,admin_sistema'],
            'status' => ['required', 'in:activo,inactivo,suspendido,eliminado'],
        ]);

        $user->update($data);

        return back()->with('status', 'Usuario actualizado.');
    }
}
