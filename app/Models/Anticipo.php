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
        'banco_id',
        'ruc',
        'importe',
        'descripcion',
        'estado',
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

    public function banco()
    {
        return $this->belongsTo(Banco::class);
    }

    public function comprobantes()
    {
        return $this->hasMany(Comprobante::class);
    }
}
