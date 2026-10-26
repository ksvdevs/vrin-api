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
        Schema::table('resoluciones', function (Blueprint $table) {
            $table->string('carta_especialista_investigacion', 160)->nullable()->after('fecha_emision');
            $table->date('fecha_carta_especialista_investigacion')->nullable()->after('carta_especialista_investigacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resoluciones', function (Blueprint $table) {
            $table->dropColumn(['carta_especialista_investigacion', 'fecha_carta_especialista_investigacion']);
        });
    }
};
