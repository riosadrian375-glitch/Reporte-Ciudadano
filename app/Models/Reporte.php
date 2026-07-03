<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reporte extends Model
{
    protected $fillable = [
        'user_id',
        'categoria_id',
        'distrito_id',
        'titulo',
        'descripcion',
        'latitud',
        'longitud',
        'direccion',
        'estado',
        'moderado',
        'estado_moderacion',
        'es_urgente',
        'clima_momento',
    ];

    protected $casts = [
        'moderado' => 'boolean',
        'es_urgente' => 'boolean',
        'clima_momento' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function distrito(): BelongsTo
    {
        return $this->belongsTo(Distrito::class);
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentario::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function guardados(): HasMany
    {
        return $this->hasMany(Guardado::class);
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class);
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(ReporteImagen::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(ReporteVideo::class);
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(ReporteEvidencia::class);
    }
}
