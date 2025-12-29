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
        Schema::create('devoluciones_reembolsos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anticipo_id')->constrained()->onDelete('cascade');
            $table->enum('tipo', ['devolucion', 'reembolso']); // devolucion=usuario, reembolso=admin
            $table->enum('metodo_pago', ['deposito_cuenta', 'deposito_caja']);
            
            // Campos para depósito en cuenta
            $table->foreignId('banco_id')->nullable()->constrained('bancos')->nullOnDelete();
            $table->enum('billetera_digital', ['yape', 'plin'])->nullable();
            $table->string('numero_operacion')->nullable();
            $table->date('fecha_deposito')->nullable();
            $table->string('archivo')->nullable(); // Foto del comprobante
            
            // Campos para depósito en caja
            $table->date('fecha_devolucion')->nullable();
            $table->text('observaciones')->nullable(); // "Entregado a contabilidad"
            
            // Campos comunes
            $table->enum('moneda', ['soles', 'dolares', 'euros']);
            $table->decimal('importe', 12, 2);
            
            // Control
            $table->foreignId('creado_por')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devoluciones_reembolsos');
    }
};
