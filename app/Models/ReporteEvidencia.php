<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteEvidencia extends Model
{
    protected $table = 'reporte_evidencias';

    protected $fillable = ['reporte_id', 'operador_id', 'comentario_resolucion', 'ruta_archivo', 'tipo_mime'];

    public function reporte(): BelongsTo
    {
        return $this->belongsTo(Reporte::class);
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operador_id');
    }
}
