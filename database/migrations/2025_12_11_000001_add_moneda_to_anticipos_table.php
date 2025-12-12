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
            $table->enum('moneda', ['soles', 'dolares', 'euros'])->default('soles')->after('importe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anticipos', function (Blueprint $table) {
            $table->dropColumn('moneda');
        });
    }
};
