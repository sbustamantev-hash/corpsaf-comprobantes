<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anticipo extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_id',
        'user_id',
        'creado_por',
        'tipo',
        'fecha',
        'moneda',
        'banco_id',
        'TipoRendicion',
        'importe',
        'descripcion',
        'estado',
        'tipo_rendicion_id',
        'aprobado_por',
        'dias_para_cerrar',
    ];

    protected $casts = [
        'fecha' => 'date',
        'importe' => 'decimal:2',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function banco()
    {
        return $this->belongsTo(Banco::class);
    }

    public function tipoRendicion()
    {
        return $this->belongsTo(TipoRendicion::class);
    }

    public function comprobantes()
    {
        return $this->hasMany(Comprobante::class);
    }

    public function devolucionesReembolsos()
    {
        return $this->hasMany(DevolucionReembolso::class);
    }

    /**
     * Calcular saldo pendiente considerando devoluciones y reembolsos aprobados
     */
    public function getSaldoPendienteAttribute()
    {
        $totalComprobado = $this->comprobantes()->where('estado', 'aprobado')->sum('monto');
        $totalDevoluciones = $this->devolucionesReembolsos()
            ->where('tipo', 'devolucion')
            ->where('estado', 'aprobado')
            ->sum('importe');
        $totalReembolsos = $this->devolucionesReembolsos()
            ->where('tipo', 'reembolso')
            ->where('estado', 'aprobado')
            ->sum('importe');
        
        return ($this->importe - $totalComprobado) - $totalDevoluciones + $totalReembolsos;
    }
}
