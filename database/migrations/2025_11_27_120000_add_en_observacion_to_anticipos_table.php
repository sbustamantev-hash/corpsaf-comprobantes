<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar el enum para incluir 'en_observacion'
        DB::statement("ALTER TABLE anticipos MODIFY COLUMN estado ENUM('pendiente', 'completo', 'aprobado', 'rechazado', 'en_observacion') DEFAULT 'pendiente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir al enum anterior
        DB::statement("ALTER TABLE anticipos MODIFY COLUMN estado ENUM('pendiente', 'completo', 'aprobado', 'rechazado') DEFAULT 'pendiente'");
    }
};

