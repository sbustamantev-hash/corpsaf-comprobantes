<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoComprobante extends Model
{
    use HasFactory;

    protected $table = 'tipos_comprobante';

    protected $fillable = [
        'codigo',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Scope para tipos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
