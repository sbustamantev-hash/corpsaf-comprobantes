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
                'nombre' => 'Empresa de Ventas',
                'codigo' => '20123456789',
                'descripcion' => 'Empresa encargada de las ventas y atención al cliente',
                'activo' => true,
            ],
            [
                'nombre' => 'Empresa de Producción',
                'codigo' => '20123456790',
                'descripcion' => 'Empresa encargada de la producción y manufactura',
                'activo' => true,
            ],
            [
                'nombre' => 'Empresa de Logística',
                'codigo' => '20123456791',
                'descripcion' => 'Empresa encargada de la logística y distribución',
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
