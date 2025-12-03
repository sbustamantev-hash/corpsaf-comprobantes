<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requerimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_id',
        'titulo',
        'detalle',
        'estado',
        'porcentaje_avance',
        'created_by',
        'updated_by',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function mensajes()
    {
        return $this->hasMany(Mensaje::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
