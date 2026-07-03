<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{
    protected $fillable = ['reporte_id', 'user_id'];

    public function reporte(): BelongsTo
    {
        return $this->belongsTo(Reporte::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
