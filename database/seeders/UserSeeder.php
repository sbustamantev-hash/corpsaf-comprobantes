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
        // Obtener Empresas
        $areaVentas = Area::where('codigo', '20123456789')->first();
        $areaProduccion = Area::where('codigo', '20123456790')->first();
        $areaLogistica = Area::where('codigo', '20123456791')->first();

        // Super Administrador (sin Empresa)
        User::updateOrCreate(
            ['dni' => '00000000', 'role' => Role::ADMIN],
            [
                'name' => 'Super Administrador',
                'email' => 'admin@corpsaf.com',
                'dni' => '00000000',
                'password' => Hash::make('admin'),
                'role' => Role::ADMIN,
                'area_id' => null,
            ]
        );

        // Eliminar usuario antiguo de marketing si existe para evitar duplicados de email
        User::where('dni', '11111111')->orWhere('email', 'marketing@corpsaf.com')->delete();

        // Administrador de Marketing
        User::updateOrCreate(
            ['dni' => '99999999', 'role' => Role::MARKETING],
            [
                'name' => 'Marketing',
                'email' => 'marketing@corpsaf.com',
                'dni' => '99999999',
                'password' => Hash::make('99999999'),
                'role' => Role::MARKETING,
                'area_id' => null, // Marketing no pertenece a ninguna área
            ]
        );


        // Administradores de Empresa
        if ($areaVentas) {
            // Crear admin de empresa
            User::updateOrCreate(
                ['dni' => '33333333', 'role' => Role::AREA_ADMIN],
                [
                    'name' => 'Admin Ventas',
                    'email' => 'admin.ventas@corpsaf.com',
                    'dni' => '33333333',
                    'password' => Hash::make('admin123'),
                    'role' => Role::AREA_ADMIN,
                    'area_id' => $areaVentas->id,
                ]
            );

            // Crear también como operador (mismo DNI, diferente rol)
            User::updateOrCreate(
                ['dni' => '33333333', 'role' => Role::OPERADOR],
                [
                    'name' => 'Admin Ventas',
                    'email' => 'admin.ventas.operador@corpsaf.com',
                    'dni' => '33333333',
                    'password' => Hash::make('admin123'),
                    'role' => Role::OPERADOR,
                    'area_id' => $areaVentas->id,
                ]
            );
        }

        if ($areaProduccion) {
            // Crear admin de empresa
            User::updateOrCreate(
                ['dni' => '22222222', 'role' => Role::AREA_ADMIN],
                [
                    'name' => 'Admin Producción',
                    'email' => 'admin.produccion@corpsaf.com',
                    'dni' => '22222222',
                    'password' => Hash::make('admin123'),
                    'role' => Role::AREA_ADMIN,
                    'area_id' => $areaProduccion->id,
                ]
            );

            // Crear también como operador (mismo DNI, diferente rol)
            User::updateOrCreate(
                ['dni' => '22222222', 'role' => Role::OPERADOR],
                [
                    'name' => 'Admin Producción',
                    'email' => 'admin.produccion.operador@corpsaf.com',
                    'dni' => '22222222',
                    'password' => Hash::make('admin123'),
                    'role' => Role::OPERADOR,
                    'area_id' => $areaProduccion->id,
                ]
            );
        }

        // Operadores - Empresa de Ventas
        if ($areaVentas) {
            $operadoresVentas = [
                '12345678' => 'Juan Pérez',
                '87654321' => 'María García',
            ];

            foreach ($operadoresVentas as $dni => $nombre) {
                User::updateOrCreate(
                    ['dni' => $dni, 'role' => Role::OPERADOR],
                    [
                        'name' => $nombre,
                        'email' => $dni . '@corpsaf.com',
                        'dni' => $dni,
                        'password' => Hash::make($dni),
                        'role' => Role::OPERADOR,
                        'area_id' => $areaVentas->id,
                    ]
                );
            }
        }

        // Operadores - Empresa de Producción
        if ($areaProduccion) {
            $operadoresProduccion = [
                '11223344' => 'Carlos López',
                '44332211' => 'Ana Martínez',
            ];

            foreach ($operadoresProduccion as $dni => $nombre) {
                User::updateOrCreate(
                    ['dni' => $dni, 'role' => Role::OPERADOR],
                    [
                        'name' => $nombre,
                        'email' => $dni . '@corpsaf.com',
                        'dni' => $dni,
                        'password' => Hash::make($dni),
                        'role' => Role::OPERADOR,
                        'area_id' => $areaProduccion->id,
                    ]
                );
            }
        }

        // Operadores - Empresa de Logística
        if ($areaLogistica) {
            User::updateOrCreate(
                ['dni' => '55667788', 'role' => Role::OPERADOR],
                [
                    'name' => 'Pedro Rodríguez',
                    'email' => '55667788@corpsaf.com',
                    'dni' => '55667788',
                    'password' => Hash::make('55667788'),
                    'role' => Role::OPERADOR,
                    'area_id' => $areaLogistica->id,
                ]
            );
        }
    }
}

