<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteVideo extends Model
{
    protected $table = 'reporte_videos';

    protected $fillable = ['reporte_id', 'ruta_archivo', 'duracion_segundos'];

    public function reporte(): BelongsTo
    {
        return $this->belongsTo(Reporte::class);
    }
}
