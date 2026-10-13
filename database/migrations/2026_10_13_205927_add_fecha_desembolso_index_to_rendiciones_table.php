<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rendiciones', function (Blueprint $table) {
            $table->index('fecha_desembolso', 'ix_rendiciones_fecha_desembolso');
        });
    }

    public function down(): void
    {
        Schema::table('rendiciones', function (Blueprint $table) {
            $table->dropIndex('ix_rendiciones_fecha_desembolso');
        });
    }
};
