<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario Admin
        User::create([
            'name' => 'Administrador',
            'email' => 'admin',
            'password' => Hash::make('admin'),
            'role' => 'admin',
        ]);

        // Usuarios Operadores/Trabajadores (DNI como user y pass)
        $dnis = [
            '12345678' => 'Juan Pérez',
            '87654321' => 'María García',
            '11223344' => 'Carlos López',
            '44332211' => 'Ana Martínez',
        ];

        foreach ($dnis as $dni => $nombre) {
            User::create([
                'name' => $nombre,
                'email' => $dni,
                'password' => Hash::make($dni),
                'role' => 'trabajador',
            ]);
        }
    }
}

