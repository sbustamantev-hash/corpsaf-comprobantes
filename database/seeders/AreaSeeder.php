<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            [
                'nombre' => 'Área de Ventas',
                'codigo' => 'AREA-VENTAS',
                'descripcion' => 'Área encargada de las ventas y atención al cliente',
                'activo' => true,
            ],
            [
                'nombre' => 'Área de Producción',
                'codigo' => 'AREA-PROD',
                'descripcion' => 'Área encargada de la producción y manufactura',
                'activo' => true,
            ],
            [
                'nombre' => 'Área de Logística',
                'codigo' => 'AREA-LOG',
                'descripcion' => 'Área encargada de la logística y distribución',
                'activo' => true,
            ],
        ];

        foreach ($areas as $areaData) {
            Area::updateOrCreate(
                ['nombre' => $areaData['nombre']],
                $areaData
            );
        }
    }
}
