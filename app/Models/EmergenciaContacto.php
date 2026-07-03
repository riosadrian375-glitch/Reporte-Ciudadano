<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmergenciaContacto extends Model
{
    protected $table = 'emergencias_contactos';

    protected $fillable = ['nombre_servicio', 'numero', 'descripcion'];
}
