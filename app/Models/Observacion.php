<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Observacion extends Model
{
    use HasFactory;

    protected $table = 'observaciones';

    protected $fillable = [
        'comprobante_id',
        'user_id',
        'mensaje',
        'tipo',
        'archivo',
    ];

    // Relación con comprobante
    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class);
    }

    // Relación con usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
