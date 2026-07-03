<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Distrito extends Model
{
    protected $fillable = ['nombre'];

    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class);
    }
}
