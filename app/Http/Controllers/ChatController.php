<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Services\ChatIAService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function show(Reporte $reporte): View
    {
        $reporte->loadMissing(['categoria', 'distrito']);

        return view('chat.show', compact('reporte'));
    }

    public function ask(Request $request, Reporte $reporte, ChatIAService $chat): View
    {
        $data = $request->validate([
            'mensaje' => ['required', 'string', 'max:500'],
        ]);

        $reporte->loadMissing(['categoria', 'distrito']);

        return view('chat.show', [
            'reporte' => $reporte,
            'mensaje' => $data['mensaje'],
            'respuesta' => $chat->responder($reporte, $data['mensaje']),
        ]);
    }
}
