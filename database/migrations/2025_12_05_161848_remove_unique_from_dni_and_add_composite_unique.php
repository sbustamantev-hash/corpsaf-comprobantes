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
        Schema::table('users', function (Blueprint $table) {
            // Remover el índice único del DNI
            $table->dropUnique(['dni']);
        });

        // Crear índice compuesto único en (dni, role) para evitar duplicados exactos
        // pero permitir el mismo DNI con diferentes roles
        Schema::table('users', function (Blueprint $table) {
            $table->unique(['dni', 'role'], 'users_dni_role_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remover el índice compuesto único
            $table->dropUnique('users_dni_role_unique');
            
            // Restaurar el índice único del DNI
            $table->unique('dni');
        });
    }
};
