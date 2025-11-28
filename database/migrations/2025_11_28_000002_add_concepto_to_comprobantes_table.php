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
        Schema::table('comprobantes', function (Blueprint $table) {
            $table->foreignId('concepto_id')->nullable()->after('tipo')->constrained('conceptos')->onDelete('restrict');
            $table->string('concepto_otro', 255)->nullable()->after('concepto_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comprobantes', function (Blueprint $table) {
            $table->dropForeign(['concepto_id']);
            $table->dropColumn(['concepto_id', 'concepto_otro']);
        });
    }
};

