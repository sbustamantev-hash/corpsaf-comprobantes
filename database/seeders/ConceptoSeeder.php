<?php

namespace Database\Seeders;

use App\Models\Concepto;
use Illuminate\Database\Seeder;

class ConceptoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conceptos = [
            [
                'nombre' => 'ALIMENTACIÓN',
                'activo' => true,
            ],
            [
                'nombre' => 'COMBUSTIBLE',
                'activo' => true,
            ],
            [
                'nombre' => 'PEAJE',
                'activo' => true,
            ],
            [
                'nombre' => 'OTROS',
                'activo' => true,
            ],
        ];

        foreach ($conceptos as $concepto) {
            Concepto::updateOrCreate(
                ['nombre' => $concepto['nombre']],
                $concepto
            );
        }
    }
}

