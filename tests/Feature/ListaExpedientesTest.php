<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use App\Services\ExpedienteWorkflow;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ListaExpedientesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        DB::unprepared(<<<'SQL'
            CREATE TABLE docentes (id INTEGER PRIMARY KEY, nombres TEXT, apellido_paterno TEXT,
                apellido_materno TEXT, dni TEXT, grado TEXT, escuela_id INTEGER, deleted_at TEXT);
            CREATE TABLE escuelas (id INTEGER PRIMARY KEY, facultad_id INTEGER, deleted_at TEXT);
            CREATE TABLE facultades (id INTEGER PRIMARY KEY, deleted_at TEXT);
            CREATE TABLE expedientes (id INTEGER PRIMARY KEY, codigo TEXT, docente_id INTEGER,
                estado TEXT, created_at TEXT, updated_at TEXT, deleted_at TEXT);
            CREATE TABLE expediente_articulos (expediente_id INTEGER PRIMARY KEY, titulo TEXT,
                base_indexadora TEXT, cuartil TEXT);
            SQL);
        DB::table('docentes')->insert([
            ['id' => 1, 'nombres' => 'Luis Ricardo', 'apellido_paterno' => 'Paredes', 'apellido_materno' => 'Quiroz', 'dni' => '41982310'],
            ['id' => 2, 'nombres' => 'Mario', 'apellido_paterno' => 'Aquino', 'apellido_materno' => 'Prueba', 'dni' => '41982311'],
        ]);
        foreach (range(1, 8) as $id) {
            DB::table('expedientes')->insert([
                'id' => $id, 'codigo' => 'EXP-'.str_pad((string) $id, 7, '0', STR_PAD_LEFT),
                'docente_id' => $id === 1 ? 1 : 2, 'estado' => $id === 2 ? 'EN_ESPERA_OPP' : 'RENDIDO',
                'created_at' => ($id === 3 ? '2025' : '2026').'-10-'.str_pad((string) $id, 2, '0', STR_PAD_LEFT).' 12:00:00',
                'deleted_at' => $id === 8 ? '2026-10-09 12:00:00' : null,
            ]);
            DB::table('expediente_articulos')->insert([
                'expediente_id' => $id, 'titulo' => $id === 1 ? 'Biopolymeric Films and Coatings' : 'Artículo de prueba',
                'base_indexadora' => 'Scopus', 'cuartil' => 'Q1',
            ]);
        }
        $usuario = new Usuario;
        $usuario->forceFill(['id' => 1, 'nombres' => 'Administrador', 'apellidos' => 'Prueba']);
        $usuario->setRelation('rolRef', new Rol(['nombre' => 'Administrador General']));
        $this->actingAs($usuario);
        $this->mock(ExpedienteWorkflow::class)->shouldReceive('transicionesDisponibles')->andReturn([]);
    }

    public function test_lista_pagina_cinco_registros_y_expone_indexacion(): void
    {
        $this->getJson('/api/expedientes')->assertOk()->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 7)->assertJsonPath('data.0.codigo', 'EXP-0000007')
            ->assertJsonPath('data.0.base_indexadora', 'Scopus')->assertJsonPath('data.0.cuartil', 'Q1');
        $this->getJson('/api/expedientes?page=2')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_busca_codigo_nombre_completo_dni_y_titulo_en_todas_las_paginas(): void
    {
        foreach (['EXP-0000001', 'Luis Ricardo Paredes', '41982310', 'Biopolymeric Films'] as $busqueda) {
            $this->getJson('/api/expedientes?'.http_build_query(['busqueda' => $busqueda]))
                ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 1);
        }
        $this->getJson('/api/expedientes?busqueda=inexistente')->assertOk()->assertJsonPath('meta.total', 0);
    }

    public function test_combina_periodo_estado_y_fechas(): void
    {
        $this->getJson('/api/expedientes?periodo=2025')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 3);
        $this->getJson('/api/expedientes?periodo=2026&estado=EN_ESPERA_OPP&busqueda=Mario&desde=2026-10-01&hasta=2026-10-03')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 2);
        $this->getJson('/api/expedientes?periodo=2025&estado=EN_ESPERA_OPP')->assertOk()->assertJsonPath('meta.total', 0);
    }

    public function test_valida_filtros_y_conserva_autorizacion(): void
    {
        $this->getJson('/api/expedientes?periodo=abc')->assertUnprocessable()->assertJsonValidationErrors('periodo');
        $this->getJson('/api/expedientes?busqueda='.str_repeat('a', 201))->assertUnprocessable()->assertJsonValidationErrors('busqueda');
        $usuario = new Usuario;
        $usuario->forceFill(['id' => 2]);
        $usuario->setRelation('rolRef', new Rol(['nombre' => 'Sin acceso']));
        $this->actingAs($usuario)->getJson('/api/expedientes')->assertForbidden();
    }
}
