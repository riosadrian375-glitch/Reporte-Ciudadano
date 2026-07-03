<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteImagen extends Model
{
    protected $table = 'reporte_imagenes';

    protected $fillable = ['reporte_id', 'ruta_archivo'];

    public function reporte(): BelongsTo
    {
        return $this->belongsTo(Reporte::class);
    }
}
