<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('docentes_historicos')
            ->whereNotIn('id', DB::table('docentes')->select('id'))
            ->whereNotIn('id', DB::table('expedientes')->select('docente_id'))
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Los registros físicos eliminados no pueden reconstruirse.
    }
};
