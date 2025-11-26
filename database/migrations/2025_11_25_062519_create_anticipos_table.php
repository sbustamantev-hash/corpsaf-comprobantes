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
        Schema::create('anticipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Usuario asignado (operador)
            $table->foreignId('creado_por')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->enum('tipo', ['anticipo', 'reembolso'])->default('anticipo');
            $table->date('fecha');
            $table->foreignId('banco_id')->nullable()->constrained('bancos')->nullOnDelete();
            $table->string('TipoRendicion', 20)->nullable();
            $table->decimal('importe', 12, 2);
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['pendiente', 'completo'])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anticipos');
    }
};
