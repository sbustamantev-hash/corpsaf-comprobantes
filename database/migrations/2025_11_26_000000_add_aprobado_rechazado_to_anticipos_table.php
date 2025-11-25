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
        // Modificar el enum para incluir 'aprobado' y 'rechazado'
        DB::statement("ALTER TABLE anticipos MODIFY COLUMN estado ENUM('pendiente', 'completo', 'aprobado', 'rechazado') DEFAULT 'pendiente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir al enum original
        DB::statement("ALTER TABLE anticipos MODIFY COLUMN estado ENUM('pendiente', 'completo') DEFAULT 'pendiente'");
    }
};

