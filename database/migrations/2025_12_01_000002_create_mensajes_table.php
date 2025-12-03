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
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requerimiento_id')->constrained('requerimientos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->text('mensaje')->nullable();
            $table->string('tipo')->default('texto'); // texto, imagen, archivo, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mensajes');
    }
};
