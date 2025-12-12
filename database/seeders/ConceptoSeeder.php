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
                'nombre' => 'ALIMENTACION',
                'activo' => true,
            ],
            [
                'nombre' => 'HOSPEDAJE',
                'activo' => true,
            ],
            [
                'nombre' => 'TRANSPORTE',
                'activo' => true,
            ],
            [
                'nombre' => 'COMBUSTIBLES',
                'activo' => true,
            ],
            [
                'nombre' => 'PEAJES',
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

