<?php

namespace Tests\Feature;

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
}
