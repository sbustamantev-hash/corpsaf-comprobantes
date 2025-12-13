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
        Schema::create('archivos_requerimiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mensaje_id')->constrained('mensajes')->onDelete('cascade');
            $table->foreignId('requerimiento_id')->constrained('requerimientos')->onDelete('cascade');
            $table->string('nombre_original');
            $table->string('ruta');
            $table->string('tipo_mime');
            $table->unsignedBigInteger('tamano');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archivos_requerimiento');
    }
};
