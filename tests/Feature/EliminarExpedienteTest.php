<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EliminarExpedienteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('PRAGMA foreign_keys = ON');
        DB::statement('CREATE TABLE expedientes (id INTEGER PRIMARY KEY, codigo TEXT, estado TEXT, updated_at TEXT, deleted_at TEXT)');
        foreach ($this->tablasDependientes() as $tabla) {
            DB::statement("CREATE TABLE {$tabla} (id INTEGER PRIMARY KEY, expediente_id INTEGER NOT NULL,
                FOREIGN KEY (expediente_id) REFERENCES expedientes(id) ON DELETE RESTRICT)");
        }
        DB::statement('CREATE TABLE auditoria (id INTEGER PRIMARY KEY, expediente_id INTEGER, accion TEXT)');
        DB::table('expedientes')->insert(['id' => 1, 'codigo' => 'ART-2026-000001', 'estado' => 'RENDIDO']);
        foreach ($this->tablasDependientes() as $tabla) {
            DB::table($tabla)->insert(['id' => 1, 'expediente_id' => 1]);
        }
        DB::table('auditoria')->insert(['id' => 1, 'expediente_id' => 1, 'accion' => 'expediente.creado']);
    }

    public function test_administrador_elimina_fisicamente_expediente_y_dependencias(): void
    {
        $this->actuarComo('Administrador General');

        $this->deleteJson('/api/expedientes/1/definitivo')->assertNoContent();

        $this->assertDatabaseMissing('expedientes', ['id' => 1]);
        foreach ($this->tablasDependientes() as $tabla) {
            $this->assertDatabaseMissing($tabla, ['expediente_id' => 1]);
        }
        $this->assertDatabaseHas('auditoria', ['id' => 1, 'expediente_id' => 1]);
        $this->deleteJson('/api/expedientes/1/definitivo')->assertNotFound();
    }

    public function test_secretaria_no_puede_eliminar_expediente_rendido(): void
    {
        $this->actuarComo('Secretaria');

        $this->deleteJson('/api/expedientes/1/definitivo')->assertForbidden();
        $this->deleteJson('/api/expedientes/1')->assertForbidden();

        $this->assertDatabaseHas('expedientes', ['id' => 1]);
        foreach ($this->tablasDependientes() as $tabla) {
            $this->assertDatabaseHas($tabla, ['expediente_id' => 1]);
        }
    }

    public function test_secretaria_no_puede_retirar_ni_eliminar_expediente_en_revision(): void
    {
        DB::table('expedientes')->where('id', 1)->update(['estado' => 'EN_REVISION_CALIDAD']);
        $this->actuarComo('Secretaria');

        $this->deleteJson('/api/expedientes/1/definitivo')->assertForbidden();
        $this->deleteJson('/api/expedientes/1')->assertForbidden();

        $this->assertDatabaseHas('expedientes', ['id' => 1, 'estado' => 'EN_REVISION_CALIDAD', 'deleted_at' => null]);
    }

    public function test_calidad_no_puede_retirar_ni_eliminar_expedientes(): void
    {
        $this->actuarComo('Calidad');

        $this->deleteJson('/api/expedientes/1/definitivo')->assertForbidden();
        $this->deleteJson('/api/expedientes/1')->assertForbidden();

        $this->assertDatabaseHas('expedientes', ['id' => 1, 'deleted_at' => null]);
    }

    public function test_administrador_puede_retirar_expediente_de_la_lista(): void
    {
        $this->actuarComo('Administrador General');

        $this->deleteJson('/api/expedientes/1')->assertNoContent();

        $this->assertNotNull(DB::table('expedientes')->where('id', 1)->value('deleted_at'));
        $this->assertDatabaseHas('resoluciones', ['expediente_id' => 1]);
    }

    private function actuarComo(string $rol): void
    {
        $usuario = new Usuario;
        $usuario->forceFill(['id' => 1]);
        $usuario->setRelation('rolRef', new Rol(['nombre' => $rol]));
        $this->actingAs($usuario);
    }

    /** @return list<string> */
    private function tablasDependientes(): array
    {
        return [
            'archivos', 'cartas_vrin', 'documentos_generados', 'expediente_articulos',
            'observaciones', 'rendiciones', 'resoluciones', 'respuestas_opp',
            'validaciones_calidad',
        ];
    }
}
