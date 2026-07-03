<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comentario extends Model
{
    protected $fillable = ['reporte_id', 'user_id', 'contenido', 'moderado', 'estado_moderacion'];

    protected $casts = [
        'moderado' => 'boolean',
    ];

    public function reporte(): BelongsTo
    {
        return $this->belongsTo(Reporte::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
