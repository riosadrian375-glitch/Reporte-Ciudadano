<?php

namespace App\Http\Controllers;

use App\Models\EmergenciaContacto;
use Illuminate\View\View;

class EmergenciaController extends Controller
{
    public function index(): View
    {
        return view('emergencias.index', [
            'contactos' => EmergenciaContacto::orderBy('nombre_servicio')->get(),
        ]);
    }
}
