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
        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new RuntimeException('La migración de docentes históricos requiere MySQL.');
        }

        if (! Schema::hasTable('docentes_historicos')) {
            Schema::create('docentes_historicos', function (Blueprint $table): void {
                $table->unsignedBigInteger('id')->primary();
                $table->char('dni', 8);
                $table->string('nombres', 100);
                $table->string('apellido_paterno', 80);
                $table->string('apellido_materno', 80)->nullable();
                $table->string('genero', 10)->nullable();
                $table->string('grado', 10)->nullable();
                $table->string('tipo_contrato', 20)->nullable();
                $table->string('categoria', 30)->nullable();
                $table->string('celular', 15)->nullable();
                $table->unsignedBigInteger('escuela_id')->nullable();
                $table->string('email', 190)->nullable();
                $table->boolean('activo')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        DB::table('docentes')->orderBy('id')->chunkById(500, function ($docentes): void {
            DB::table('docentes_historicos')->insertOrIgnore($docentes->map(fn ($docente): array => [
                'id' => $docente->id,
                'dni' => $docente->dni,
                'nombres' => $docente->nombres,
                'apellido_paterno' => $docente->apellido_paterno,
                'apellido_materno' => $docente->apellido_materno,
                'genero' => $docente->genero,
                'grado' => $docente->grado,
                'tipo_contrato' => $docente->tipo_contrato,
                'categoria' => $docente->categoria,
                'celular' => $docente->celular,
                'escuela_id' => $docente->escuela_id,
                'email' => $docente->email,
                'activo' => $docente->activo,
                'created_at' => $docente->created_at,
                'updated_at' => $docente->updated_at,
                'deleted_at' => $docente->deleted_at,
            ])->all());
        });

        $referencia = collect(Schema::getForeignKeys('expedientes'))->first(
            fn (array $clave): bool => $clave['columns'] === ['docente_id']
        );
        if ($referencia === null || ! in_array($referencia['foreign_table'], ['docentes', 'docentes_historicos'], true)) {
            throw new RuntimeException('La clave foránea de expedientes.docente_id no coincide con el esquema esperado.');
        }
        if ($referencia['foreign_table'] === 'docentes') {
            DB::statement("ALTER TABLE `expedientes` DROP FOREIGN KEY `{$referencia['name']}`, ADD CONSTRAINT `fk_exp_docente_historico` FOREIGN KEY (`docente_id`) REFERENCES `docentes_historicos` (`id`)");
        }
        DB::table('docentes')->whereNotNull('deleted_at')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('expedientes')->whereNotIn('docente_id', DB::table('docentes')->select('id'))->exists()) {
            throw new RuntimeException('No se puede revertir: hay expedientes de docentes eliminados.');
        }

        DB::statement('ALTER TABLE `expedientes` DROP FOREIGN KEY `fk_exp_docente_historico`, ADD CONSTRAINT `fk_exp_docente` FOREIGN KEY (`docente_id`) REFERENCES `docentes` (`id`)');
        Schema::dropIfExists('docentes_historicos');
    }
};
