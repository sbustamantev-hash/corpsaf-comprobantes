<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuraciones';
    
    protected $fillable = ['clave', 'valor', 'tipo'];

    /**
     * Obtener el valor de una configuración por su clave
     */
    public static function obtener($clave, $default = null)
    {
        $config = self::where('clave', $clave)->first();
        return $config ? $config->valor : $default;
    }

    /**
     * Establecer el valor de una configuración
     */
    public static function establecer($clave, $valor, $tipo = 'text')
    {
        return self::updateOrCreate(
            ['clave' => $clave],
            ['valor' => $valor, 'tipo' => $tipo]
        );
    }
}
