<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asignacion extends Model
{
    protected $table = 'asignaciones';

    protected $fillable = ['reporte_id', 'operador_id', 'admin_id', 'estado'];

    public function reporte(): BelongsTo
    {
        return $this->belongsTo(Reporte::class);
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operador_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
