<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Relación con usuarios
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Obtener usuarios administradores del Empresa
     */
    public function administradores()
    {
        return $this->users()->where('role', 'area_admin');
    }

    /**
     * Obtener operadores del Empresa
     */
    public function operadores()
    {
        return $this->users()->whereIn('role', ['operador', 'trabajador']);
    }

    /**
     * Relación con comprobantes a través de usuarios
     */
    public function comprobantes()
    {
        return $this->hasManyThrough(
            Comprobante::class,
            User::class,
            'area_id', // Foreign key en users
            'user_id', // Foreign key en comprobantes
            'id',      // Local key en areas
            'id'       // Local key en users
        );
    }

    /**
     * Scope para Empresas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Relación con anticipos
     */
    public function anticipos()
    {
        return $this->hasMany(Anticipo::class);
    }
}
