<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('numero_serie', 4)->nullable()->after('telefono')->comment('Número de serie (4 dígitos)');
            $table->string('numero_comprobante', 10)->nullable()->after('numero_serie')->comment('Número de comprobante (10 dígitos con ceros a la izquierda)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['numero_serie', 'numero_comprobante']);
        });
    }
};
