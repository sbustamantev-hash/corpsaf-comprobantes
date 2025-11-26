<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\TipoRendicion;
class TipoRendicionSeeder extends Seeder
{

    public function run(): void
    {
        $Rendicion = [
            ['codigo' => '01', 'descripcion' => 'VIATICOS', 'activo' => true],
            ['codigo' => '02', 'descripcion' => 'COMISION DE SERVICIOS', 'activo' => true],
            ['codigo' => '03', 'descripcion' => 'GASTOS DE REPRESENTACION', 'activo' => true],
            ['codigo' => '04', 'descripcion' => 'GASTOS DE OPERACION', 'activo' => true],


        ];

        foreach ($Rendicion as $tiporendicion) {
            TipoRendicion::updateOrCreate(
                ['codigo' => $tiporendicion['codigo']],
                $tiporendicion
            );
        }
    }
}
