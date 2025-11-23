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
    Schema::create('comprobantes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('tipo'); // boleta, recibo, vale...
        $table->decimal('monto', 10, 2);
        $table->date('fecha');
        $table->text('detalle')->nullable();
        $table->string('archivo')->nullable(); // ruta de la imagen o pdf
        $table->string('estado')->default('pendiente'); // pendiente, aprobado, rechazado
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobantes');
    }
};
