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
            $table->foreignId('aprobado_por')->nullable()->after('estado')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anticipos', function (Blueprint $table) {
            $table->dropForeign(['aprobado_por']);
            $table->dropColumn('aprobado_por');
        });
    }
};
