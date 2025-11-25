<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoDocumento;

class TipoDocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Tipos de documento según SUNAT (Perú)
     */
    public function run(): void
    {
        $tiposDocumento = [
            [
                'codigo' => '0',
                'descripcion' => 'OTROS TIPOS DE DOCUMENTOS',
                'activo' => true,
            ],
            [
                'codigo' => '1',
                'descripcion' => 'DOCUMENTO NACIONAL DE IDENTIDAD (DNI)',
                'activo' => true,
            ],
            [
                'codigo' => '4',
                'descripcion' => 'CARNET DE EXTRANJERIA',
                'activo' => true,
            ],
            [
                'codigo' => '6',
                'descripcion' => 'REGISTRO ÚNICO DE CONTRIBUYENTES',
                'activo' => true,
            ],
            [
                'codigo' => '7',
                'descripcion' => 'PASAPORTE',
                'activo' => true,
            ],
            [
                'codigo' => 'A',
                'descripcion' => 'CÉDULA DIPLOMÁTICA DE IDENTIDAD',
                'activo' => true,
            ],
        ];

        foreach ($tiposDocumento as $tipo) {
            TipoDocumento::updateOrCreate(
                ['codigo' => $tipo['codigo']],
                $tipo
            );
        }
    }
}
