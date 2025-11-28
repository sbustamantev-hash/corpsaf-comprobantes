<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concepto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Convertir nombre a mayúsculas al guardar
     */
    public function setNombreAttribute($value)
    {
        $this->attributes['nombre'] = strtoupper(trim($value));
    }
}

