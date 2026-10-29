<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plantillas', function (Blueprint $table) {
            $table->unsignedBigInteger('familia_id')->nullable()->index();
        });

        $familias = [];
        foreach (DB::table('plantillas')->orderBy('id')->get(['id', 'nombre', 'modulo', 'tipo_documento_id', 'version']) as $plantilla) {
            $clave = $plantilla->modulo.'|'.$plantilla->tipo_documento_id.'|'.$plantilla->nombre;
            if ((int) $plantilla->version === 1 || ! isset($familias[$clave])) {
                $familias[$clave] = $plantilla->id;
            }
            DB::table('plantillas')->where('id', $plantilla->id)->update(['familia_id' => $familias[$clave]]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plantillas', function (Blueprint $table) {
            $table->dropColumn('familia_id');
        });
    }
};
