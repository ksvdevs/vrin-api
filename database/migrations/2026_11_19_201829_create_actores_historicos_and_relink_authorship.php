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
        $this->validarMotor();

        if (! Schema::hasTable('actores_historicos')) {
            Schema::create('actores_historicos', function (Blueprint $table): void {
                $table->unsignedBigInteger('id')->primary();
                $table->string('nombres', 100);
                $table->string('apellidos', 150);
                $table->string('rol_nombre', 100);
                $table->timestamp('fecha_alta')->nullable();
                $table->timestamp('archivado_at')->nullable();
            });
        }

        DB::table('usuarios')
            ->leftJoin('roles', 'roles.id', '=', 'usuarios.rol_id')
            ->select('usuarios.id', 'usuarios.nombres', 'usuarios.apellidos', 'roles.nombre as rol_nombre', 'usuarios.created_at', 'usuarios.deleted_at')
            ->chunkById(500, function ($usuarios): void {
                DB::table('actores_historicos')->insertOrIgnore($usuarios->map(fn ($usuario): array => [
                    'id' => $usuario->id,
                    'nombres' => $usuario->nombres,
                    'apellidos' => $usuario->apellidos,
                    'rol_nombre' => $usuario->rol_nombre ?? 'Sin rol',
                    'fecha_alta' => $usuario->created_at,
                    'archivado_at' => $usuario->deleted_at,
                ])->all());
            }, 'usuarios.id', 'id');

        $this->validarReferencias();
        $this->cambiarReferencias('actores_historicos');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->validarMotor();

        if (DB::table('actores_historicos')->whereNotIn('id', DB::table('usuarios')->select('id'))->exists()) {
            throw new RuntimeException('No se puede revertir: existen autores históricos cuyas cuentas ya fueron eliminadas.');
        }

        $this->validarReferencias();
        $this->cambiarReferencias('usuarios');

        Schema::dropIfExists('actores_historicos');
    }

    /**
     * @return array<int, array{string, string, string}>
     */
    private function referencias(): array
    {
        return [
            ['expedientes', 'created_by', 'fk_exp_creador'],
            ['expedientes', 'updated_by', 'fk_exp_editor'],
            ['validaciones_calidad', 'validado_por', 'fk_val_user'],
            ['cartas_vrin', 'emitida_por', 'fk_cv_user'],
            ['respuestas_opp', 'registrado_por', 'fk_opp_user'],
            ['resoluciones', 'emitida_por', 'fk_res_user'],
            ['rendiciones', 'cerrada_por', 'fk_rend_user'],
            ['archivos', 'subido_por', 'fk_arch_user'],
            ['plantillas', 'created_by', 'fk_pl_user'],
            ['plantilla_seleccionada', 'seleccionado_por', 'fk_sel_user'],
            ['documentos_generados', 'generado_por', 'fk_dg_user'],
            ['observaciones', 'creada_por', 'fk_obs_user'],
        ];
    }

    private function validarMotor(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new RuntimeException('La migración de autoría histórica requiere MySQL.');
        }
    }

    private function validarReferencias(): void
    {
        if (DB::connection()->pretending()) {
            return;
        }

        foreach ($this->referencias() as [$tabla, $columna, $clave]) {
            $foreignKey = $this->referenciaActual($tabla, $columna, $clave);
            if ($foreignKey['columns'] !== [$columna]
                || ! in_array($foreignKey['foreign_table'], ['usuarios', 'actores_historicos'], true)) {
                throw new RuntimeException("La clave foránea {$clave} no coincide con el esquema esperado.");
            }

            $faltantes = DB::table($tabla)
                ->leftJoin('actores_historicos as actor', 'actor.id', '=', $tabla.'.'.$columna)
                ->whereNotNull($tabla.'.'.$columna)
                ->whereNull('actor.id')
                ->exists();
            if ($faltantes) {
                throw new RuntimeException("Existen autores sin registro histórico en {$tabla}.{$columna}.");
            }
        }
    }

    private function cambiarReferencias(string $destino): void
    {
        $porTabla = [];
        foreach ($this->referencias() as [$tabla, $columna, $clave]) {
            $porTabla[$tabla][] = [$columna, $clave];
        }

        foreach ($porTabla as $tabla => $referencias) {
            $clausulas = [];
            foreach ($referencias as [$columna, $clave]) {
                $actual = $this->referenciaActual($tabla, $columna, $clave, $destino);
                $nuevaClave = $destino === 'actores_historicos' ? $clave.'_actor' : $clave;
                if ($actual['name'] === $nuevaClave && $actual['foreign_table'] === $destino) {
                    continue;
                }

                $clausulas[] = "DROP FOREIGN KEY `{$actual['name']}`";
                $clausulas[] = "ADD CONSTRAINT `{$nuevaClave}` FOREIGN KEY (`{$columna}`) REFERENCES `{$destino}` (`id`)";
            }

            if ($clausulas !== []) {
                DB::statement("ALTER TABLE `{$tabla}` ".implode(', ', $clausulas));
            }
        }
    }

    /**
     * @return array{name: string, columns: array<int, string>, foreign_table: string}
     */
    private function referenciaActual(string $tabla, string $columna, string $clave, ?string $destino = null): array
    {
        if (DB::connection()->pretending()) {
            return [
                'name' => $destino === 'usuarios' ? $clave.'_actor' : $clave,
                'columns' => [$columna],
                'foreign_table' => $destino === 'usuarios' ? 'actores_historicos' : 'usuarios',
            ];
        }

        $foreignKey = collect(Schema::getForeignKeys($tabla))->first(
            fn (array $key): bool => in_array($key['name'], [$clave, $clave.'_actor'], true)
        );

        if ($foreignKey === null) {
            throw new RuntimeException("No se encontró la clave foránea {$clave} de {$tabla}.");
        }

        return $foreignKey;
    }
};
