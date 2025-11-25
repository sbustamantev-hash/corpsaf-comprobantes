<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Area;
use App\Enums\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener áreas
        $areaVentas = Area::where('codigo', 'AREA-VENTAS')->first();
        $areaProduccion = Area::where('codigo', 'AREA-PROD')->first();
        $areaLogistica = Area::where('codigo', 'AREA-LOG')->first();

        // Super Administrador (sin área)
        User::updateOrCreate(
            ['email' => 'admin'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('admin'),
                'role' => Role::ADMIN,
                'area_id' => null,
            ]
        );

        // Administradores de Área
        if ($areaVentas) {
            User::updateOrCreate(
                ['email' => 'admin.ventas'],
                [
                    'name' => 'Admin Ventas',
                    'password' => Hash::make('admin123'),
                    'role' => Role::AREA_ADMIN,
                    'area_id' => $areaVentas->id,
                ]
            );
        }

        if ($areaProduccion) {
            User::updateOrCreate(
                ['email' => 'admin.produccion'],
                [
                    'name' => 'Admin Producción',
                    'password' => Hash::make('admin123'),
                    'role' => Role::AREA_ADMIN,
                    'area_id' => $areaProduccion->id,
                ]
            );
        }

        // Operadores - Área de Ventas
        if ($areaVentas) {
            $operadoresVentas = [
                '12345678' => 'Juan Pérez',
                '87654321' => 'María García',
            ];

            foreach ($operadoresVentas as $dni => $nombre) {
                User::updateOrCreate(
                    ['email' => $dni],
                    [
                        'name' => $nombre,
                        'password' => Hash::make($dni),
                        'role' => Role::OPERADOR,
                        'area_id' => $areaVentas->id,
                    ]
                );
            }
        }

        // Operadores - Área de Producción
        if ($areaProduccion) {
            $operadoresProduccion = [
                '11223344' => 'Carlos López',
                '44332211' => 'Ana Martínez',
            ];

            foreach ($operadoresProduccion as $dni => $nombre) {
                User::updateOrCreate(
                    ['email' => $dni],
                    [
                        'name' => $nombre,
                        'password' => Hash::make($dni),
                        'role' => Role::OPERADOR,
                        'area_id' => $areaProduccion->id,
                    ]
                );
            }
        }

        // Operadores - Área de Logística
        if ($areaLogistica) {
            User::updateOrCreate(
                ['email' => '55667788'],
                [
                    'name' => 'Pedro Rodríguez',
                    'password' => Hash::make('55667788'),
                    'role' => Role::OPERADOR,
                    'area_id' => $areaLogistica->id,
                ]
            );
        }
    }
}

