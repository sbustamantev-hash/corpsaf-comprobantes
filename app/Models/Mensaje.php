<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    use HasFactory;

    protected $fillable = [
        'requerimiento_id',
        'user_id',
        'mensaje',
        'tipo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requerimiento()
    {
        return $this->belongsTo(Requerimiento::class);
    }

    public function archivos()
    {
        return $this->hasMany(ArchivoRequerimiento::class);
    }
}
