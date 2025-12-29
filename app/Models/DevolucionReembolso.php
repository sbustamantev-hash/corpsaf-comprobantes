<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevolucionReembolso extends Model
{
    use HasFactory;

    // Billeteras digitales hardcodeadas
    const BILLETERAS_DIGITALES = [
        'yape' => 'Yape',
        'plin' => 'Plin',
    ];

    protected $fillable = [
        'anticipo_id',
        'tipo',
        'metodo_pago',
        'banco_id',
        'billetera_digital',
        'numero_operacion',
        'fecha_deposito',
        'fecha_devolucion',
        'archivo',
        'moneda',
        'importe',
        'observaciones',
        'creado_por',
        'estado',
    ];

    protected $casts = [
        'fecha_deposito' => 'date',
        'fecha_devolucion' => 'date',
        'importe' => 'decimal:2',
    ];

    public function anticipo()
    {
        return $this->belongsTo(Anticipo::class);
    }

    public function banco()
    {
        return $this->belongsTo(Banco::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * Obtener las billeteras digitales disponibles
     */
    public static function getBilleterasDigitales()
    {
        return self::BILLETERAS_DIGITALES;
    }
}
