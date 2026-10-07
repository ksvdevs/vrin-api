<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rendiciones', function (Blueprint $table) {
            $table->decimal('monto_desembolsado', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rendiciones', function (Blueprint $table) {
            $table->dropColumn('monto_desembolsado');
        });
    }
};
