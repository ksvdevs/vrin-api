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
        Schema::table('documentos_generados', function (Blueprint $table) {
            $table->string('drive_file_id')->nullable();
            $table->string('drive_folder_id')->nullable();
            $table->timestamp('drive_synced_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos_generados', function (Blueprint $table) {
            $table->dropColumn(['drive_file_id', 'drive_folder_id', 'drive_synced_at']);
        });
    }
};
