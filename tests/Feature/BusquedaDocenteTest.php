<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BusquedaDocenteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared(<<<'SQL'
            CREATE TABLE docentes (id INTEGER PRIMARY KEY, dni TEXT, nombres TEXT,
                apellido_paterno TEXT, apellido_materno TEXT, activo INTEGER,
                escuela_id INTEGER, deleted_at TEXT);
            CREATE TABLE escuelas (id INTEGER PRIMARY KEY, facultad_id INTEGER, deleted_at TEXT);
            CREATE TABLE facultades (id INTEGER PRIMARY KEY, nombre TEXT, deleted_at TEXT);
            SQL);
        DB::table('facultades')->insert(['id' => 1, 'nombre' => 'Ingeniería']);
        DB::table('escuelas')->insert(['id' => 1, 'facultad_id' => 1]);
        DB::table('docentes')->insert([
            ['id' => 1, 'dni' => '77100101', 'nombres' => 'Delmer', 'apellido_paterno' => 'Zea', 'apellido_materno' => 'Gonzales', 'activo' => 1, 'escuela_id' => 1],
            ['id' => 2, 'dni' => '77100102', 'nombres' => 'Delmer', 'apellido_paterno' => 'Zea', 'apellido_materno' => 'Rojas', 'activo' => 1, 'escuela_id' => 1],
            ['id' => 3, 'dni' => '77100103', 'nombres' => 'Delmer', 'apellido_paterno' => 'Zea', 'apellido_materno' => 'Gonzales', 'activo' => 0, 'escuela_id' => 1],
        ]);

        $usuario = new Usuario;
        $usuario->forceFill(['id' => 1]);
        $usuario->setRelation('rolRef', new Rol(['nombre' => 'Administrador General']));
        $this->actingAs($usuario);
    }

    public function test_busca_nombre_completo_sin_depender_del_orden_de_nombres_y_apellidos(): void
    {
        foreach (['Delmer Zea Gonzales', 'Zea Gonzales Delmer', '77100101'] as $busqueda) {
            $this->getJson('/api/docentes?'.http_build_query(['q' => $busqueda]))
                ->assertOk()->assertJsonCount(1)->assertJsonPath('0.id', 1)
                ->assertJsonPath('0.escuela.facultad.nombre', 'Ingeniería');
        }
    }

    public function test_la_busqueda_del_formulario_no_usa_dni(): void
    {
        $this->getJson('/api/docentes?'.http_build_query(['q' => 'Delmer Zea', 'solo_nombre' => 1]))
            ->assertOk()->assertJsonCount(2);
        $this->getJson('/api/docentes?'.http_build_query(['q' => '77100101', 'solo_nombre' => 1]))
            ->assertOk()->assertJsonCount(0);
    }
}
