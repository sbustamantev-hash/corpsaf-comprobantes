<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    use HasFactory;

    protected $table = 'comprobantes';

    protected $fillable = [
        'user_id',
        'anticipo_id',
        'tipo',
        'concepto_id',
        'concepto_otro',
        'serie',
        'numero',
        'ruc_empresa',
        'monto',
        'moneda',
        'fecha',
        'detalle',
        'archivo',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // Relación con usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con observaciones
    public function observaciones()
    {
        return $this->hasMany(Observacion::class)->orderBy('created_at', 'asc');
    }

    public function anticipo()
    {
        return $this->belongsTo(Anticipo::class);
    }

    // Relación con concepto
    public function concepto()
    {
        return $this->belongsTo(Concepto::class);
    }

    // Obtener el tipo de comprobante basado en el código
    public function tipoComprobante()
    {
        return \App\Models\TipoComprobante::where('codigo', $this->tipo)->first();
    }
}
