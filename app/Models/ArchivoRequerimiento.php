<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivoRequerimiento extends Model
{
    use HasFactory;

    protected $table = 'archivos_requerimiento';

    protected $fillable = [
        'mensaje_id',
        'requerimiento_id',
        'nombre_original',
        'ruta',
        'tipo_mime',
        'tamano',
        'uploaded_by',
    ];

    /**
     * Get the message that owns the file.
     */
    public function mensaje()
    {
        return $this->belongsTo(Mensaje::class);
    }

    /**
     * Get the requirement that owns the file.
     */
    public function requerimiento()
    {
        return $this->belongsTo(Requerimiento::class);
    }

    /**
     * Get the user that uploaded the file.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
