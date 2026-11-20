<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FacultadEscuelasTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::unprepared(<<<'SQL'
            CREATE TABLE facultades (id INTEGER PRIMARY KEY, nombre TEXT, activo INTEGER);
            CREATE TABLE escuelas (id INTEGER PRIMARY KEY, facultad_id INTEGER, nombre TEXT, activo INTEGER);
            SQL);

        DB::table('facultades')->insert([
            ['id' => 1, 'nombre' => 'Ingeniería', 'activo' => 1],
            ['id' => 2, 'nombre' => 'Salud', 'activo' => 1],
            ['id' => 3, 'nombre' => 'Inactiva', 'activo' => 0],
        ]);
        DB::table('escuelas')->insert([
            ['id' => 1, 'facultad_id' => 1, 'nombre' => 'Ingeniería Civil', 'activo' => 1],
            ['id' => 2, 'facultad_id' => 1, 'nombre' => 'Ingeniería de Minas', 'activo' => 1],
            ['id' => 3, 'facultad_id' => 1, 'nombre' => 'Carrera inactiva', 'activo' => 0],
            ['id' => 4, 'facultad_id' => 2, 'nombre' => 'Enfermería', 'activo' => 1],
        ]);

        $usuario = new Usuario;
        $usuario->forceFill(['id' => 1]);
        $usuario->setRelation('rolRef', new Rol(['nombre' => 'Secretaria']));
        $this->actingAs($usuario);
    }

    public function test_lista_solo_las_carreras_activas_de_la_facultad_seleccionada(): void
    {
        $this->getJson('/api/facultades/1/escuelas')
            ->assertOk()
            ->assertExactJson([
                ['id' => 1, 'nombre' => 'Ingeniería Civil'],
                ['id' => 2, 'nombre' => 'Ingeniería de Minas'],
            ]);

        $this->getJson('/api/facultades/3/escuelas')->assertNotFound();
    }
}
