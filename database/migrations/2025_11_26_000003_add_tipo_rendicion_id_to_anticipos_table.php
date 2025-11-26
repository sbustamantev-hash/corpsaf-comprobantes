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
        Schema::table('anticipos', function (Blueprint $table) {
            $table->foreignId('tipo_rendicion_id')->nullable()->constrained('tipos_rendicion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anticipos', function (Blueprint $table) {
            $table->dropForeign(['tipo_rendicion_id']);
            $table->dropColumn('tipo_rendicion_id');
        });
    }
};
