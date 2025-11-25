<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banco;

class BancoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Bancos y entidades financieras según SUNAT (Perú)
     */
    public function run(): void
    {
        $bancos = [
            ['codigo' => '01', 'descripcion' => 'CENTRAL RESERVA DEL PERU', 'activo' => true],
            ['codigo' => '02', 'descripcion' => 'DE CREDITO DEL PERU', 'activo' => true],
            ['codigo' => '03', 'descripcion' => 'INTERNACIONAL DEL PERU', 'activo' => true],
            ['codigo' => '05', 'descripcion' => 'LATINO', 'activo' => true],
            ['codigo' => '07', 'descripcion' => 'CITIBANK DEL PERU S.A.', 'activo' => true],
            ['codigo' => '08', 'descripcion' => 'STANDARD CHARTERED', 'activo' => true],
            ['codigo' => '09', 'descripcion' => 'SCOTIABANK PERU', 'activo' => true],
            ['codigo' => '11', 'descripcion' => 'CONTINENTAL', 'activo' => true],
            ['codigo' => '12', 'descripcion' => 'DE LIMA', 'activo' => true],
            ['codigo' => '16', 'descripcion' => 'MERCANTIL', 'activo' => true],
            ['codigo' => '18', 'descripcion' => 'NACION', 'activo' => true],
            ['codigo' => '22', 'descripcion' => 'SANTANDER CENTRAL HISPANO', 'activo' => true],
            ['codigo' => '23', 'descripcion' => 'DE COMERCIO', 'activo' => true],
            ['codigo' => '25', 'descripcion' => 'REPUBLICA', 'activo' => true],
            ['codigo' => '26', 'descripcion' => 'NBK BANK', 'activo' => true],
            ['codigo' => '29', 'descripcion' => 'BANCOSUR', 'activo' => true],
            ['codigo' => '35', 'descripcion' => 'FINANCIERO DEL PERU', 'activo' => true],
            ['codigo' => '37', 'descripcion' => 'DEL PROGRESO', 'activo' => true],
            ['codigo' => '38', 'descripcion' => 'INTERAMERICANO FINANZAS', 'activo' => true],
            ['codigo' => '39', 'descripcion' => 'BANEX', 'activo' => true],
            ['codigo' => '40', 'descripcion' => 'NUEVO MUNDO', 'activo' => true],
            ['codigo' => '41', 'descripcion' => 'SUDAMERICANO', 'activo' => true],
            ['codigo' => '42', 'descripcion' => 'DEL LIBERTADOR', 'activo' => true],
            ['codigo' => '43', 'descripcion' => 'DEL TRABAJO', 'activo' => true],
            ['codigo' => '44', 'descripcion' => 'SOLVENTA', 'activo' => true],
            ['codigo' => '45', 'descripcion' => 'SERBANCO SA.', 'activo' => true],
            ['codigo' => '46', 'descripcion' => 'BANK OF BOSTON', 'activo' => true],
            ['codigo' => '47', 'descripcion' => 'ORION', 'activo' => true],
            ['codigo' => '48', 'descripcion' => 'DEL PAIS', 'activo' => true],
            ['codigo' => '49', 'descripcion' => 'MI BANCO', 'activo' => true],
            ['codigo' => '50', 'descripcion' => 'BNP PARIBAS', 'activo' => true],
            ['codigo' => '51', 'descripcion' => 'AGROBANCO', 'activo' => true],
            ['codigo' => '53', 'descripcion' => 'HSBC BANK PERU S.A.', 'activo' => true],
            ['codigo' => '54', 'descripcion' => 'BANCO FALABELLA S.A.', 'activo' => true],
            ['codigo' => '55', 'descripcion' => 'BANCO RIPLEY', 'activo' => true],
            ['codigo' => '56', 'descripcion' => 'BANCO SANTANDER PERU S.A.', 'activo' => true],
            ['codigo' => '58', 'descripcion' => 'BANCO AZTECA DEL PERU', 'activo' => true],
            ['codigo' => '99', 'descripcion' => 'OTROS', 'activo' => true],
        ];

        foreach ($bancos as $banco) {
            Banco::updateOrCreate(
                ['codigo' => $banco['codigo']],
                $banco
            );
        }
    }
}
