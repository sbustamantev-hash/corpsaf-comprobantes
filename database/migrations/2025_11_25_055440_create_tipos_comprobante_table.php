<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tipos_comprobante', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique()->comment('Código del tipo de comprobante según SUNAT (00, 01, 02, etc.)');
            $table->text('descripcion')->comment('Descripción del tipo de comprobante de pago o documento');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_comprobante');
    }
};
