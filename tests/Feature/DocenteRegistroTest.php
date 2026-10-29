<?php

namespace Tests\Feature;

use App\Models\Expediente;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DocenteRegistroTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared(<<<'SQL'
            CREATE TABLE facultades (id INTEGER PRIMARY KEY, nombre TEXT, deleted_at TEXT);
            CREATE TABLE escuelas (id INTEGER PRIMARY KEY, facultad_id INTEGER, deleted_at TEXT);
            CREATE TABLE docentes (id INTEGER PRIMARY KEY, dni TEXT UNIQUE, nombres TEXT,
                apellido_paterno TEXT, apellido_materno TEXT, genero TEXT, celular TEXT,
                grado TEXT, tipo_contrato TEXT, categoria TEXT, escuela_id INTEGER, email TEXT, activo INTEGER,
                created_at TEXT, updated_at TEXT, deleted_at TEXT);
            SQL);
        DB::table('facultades')->insert(['id' => 1, 'nombre' => 'Ingeniería']);
        DB::table('escuelas')->insert(['id' => 1, 'facultad_id' => 1]);

        $usuario = new Usuario;
        $usuario->forceFill(['id' => 1]);
        $usuario->setRelation('rolRef', new Rol(['nombre' => 'Administrador General']));
        $this->actingAs($usuario);
    }

    public function test_registra_y_actualiza_genero_y_celular(): void
    {
        $datos = [
            'dni' => '98712345',
            'nombres' => 'María',
            'apellido_paterno' => 'Quispe',
            'genero' => 'FEMENINO',
            'celular' => '987123456',
            'grado' => 'Dra.',
            'categoria' => 'ORDINARIO_AUXILIAR',
            'escuela_id' => 1,
            'email' => 'maria@example.com',
            'activo' => false,
        ];

        $this->postJson('/api/docentes', $datos)
            ->assertCreated()
            ->assertJsonPath('genero', 'FEMENINO')
            ->assertJsonPath('celular', '987123456')
            ->assertJsonPath('categoria', 'ORDINARIO_AUXILIAR')
            ->assertJsonPath('tipo_contrato', 'NOMBRADO')
            ->assertJsonPath('activo', false);

        $this->putJson('/api/docentes/1', [...$datos, 'genero' => 'OTRO', 'celular' => null, 'categoria' => 'CONTRATADO', 'activo' => true])
            ->assertOk()
            ->assertJsonPath('genero', 'OTRO')
            ->assertJsonPath('celular', null)
            ->assertJsonPath('tipo_contrato', 'CONTRATADO')
            ->assertJsonPath('activo', true);

        $this->assertDatabaseHas('docentes', ['id' => 1, 'genero' => 'OTRO', 'celular' => null, 'categoria' => 'CONTRATADO']);
    }

    public function test_exige_genero_y_valida_celular_en_el_registro(): void
    {
        $this->postJson('/api/docentes', [
            'dni' => '98712345',
            'nombres' => 'María',
            'apellido_paterno' => 'Quispe',
            'celular' => '12345',
            'grado' => 'Dra.',
            'categoria' => 'ORDINARIO_AUXILIAR',
            'escuela_id' => 1,
        ])->assertUnprocessable()->assertJsonValidationErrors(['genero', 'celular', 'email', 'activo']);

        $this->assertDatabaseCount('docentes', 0);
    }

    public function test_crud_y_baja_logica_conservan_el_docente_del_expediente(): void
    {
        $datos = [
            'dni' => '98712345',
            'nombres' => 'Maria',
            'apellido_paterno' => 'Quispe',
            'genero' => 'FEMENINO',
            'grado' => 'Dra.',
            'categoria' => 'ORDINARIO_AUXILIAR',
            'escuela_id' => 1,
            'email' => 'maria@example.com',
            'activo' => true,
        ];

        $this->postJson('/api/docentes', $datos)->assertCreated()->assertJsonPath('id', 1);
        $this->getJson('/api/docentes/1')->assertOk()->assertJsonPath('dni', '98712345');
        $this->getJson('/api/docentes?busqueda=Quispe')->assertOk()
            ->assertJsonPath('total', 1)->assertJsonPath('data.0.id', 1);
        $this->putJson('/api/docentes/1', [...$datos, 'nombres' => 'Maria Elena'])
            ->assertOk()->assertJsonPath('nombres', 'Maria Elena');

        DB::statement('CREATE TABLE expedientes (id INTEGER PRIMARY KEY, docente_id INTEGER, deleted_at TEXT)');
        DB::table('expedientes')->insert(['id' => 1, 'docente_id' => 1]);

        $this->deleteJson('/api/docentes/1')->assertNoContent();
        $this->assertSoftDeleted('docentes', ['id' => 1, 'activo' => 0]);
        $this->getJson('/api/docentes/1')->assertNotFound();
        $this->getJson('/api/docentes')->assertOk()->assertJsonPath('total', 0);
        $this->assertSame('Maria Elena', Expediente::find(1)->docente->nombres);
    }

    public function test_cambio_de_estado_solo_actualiza_activo_y_exige_autorizacion(): void
    {
        DB::table('docentes')->insert([
            'id' => 1, 'dni' => '98712345', 'nombres' => 'Maria', 'apellido_paterno' => 'Quispe',
            'grado' => 'Dra.', 'tipo_contrato' => 'CONTRATADO', 'categoria' => 'CONTRATADO',
            'escuela_id' => 1, 'activo' => 1,
        ]);

        $this->patchJson('/api/docentes/1/estado', [])->assertUnprocessable()->assertJsonValidationErrors('activo');
        $this->patchJson('/api/docentes/1/estado', ['activo' => 'incorrecto'])
            ->assertUnprocessable()->assertJsonValidationErrors('activo');
        $this->patchJson('/api/docentes/1/estado', ['activo' => false, 'nombres' => 'Alterado'])
            ->assertOk()->assertJsonPath('activo', false)->assertJsonPath('tipo_contrato', 'CONTRATADO');
        $this->assertDatabaseHas('docentes', ['id' => 1, 'nombres' => 'Maria', 'categoria' => 'CONTRATADO', 'activo' => 0]);

        $usuario = new Usuario;
        $usuario->forceFill(['id' => 2]);
        $usuario->setRelation('rolRef', new Rol(['nombre' => 'Secretaria']));
        $this->actingAs($usuario);
        $this->getJson('/api/docentes/1')->assertOk();
        $this->patchJson('/api/docentes/1/estado', ['activo' => true])->assertForbidden();
        $this->putJson('/api/docentes/1', ['activo' => true])->assertForbidden();
        $this->deleteJson('/api/docentes/1')->assertForbidden();
    }
}
