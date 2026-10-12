<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ListaRolesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::connection()->getDriverName());
        DB::unprepared('CREATE TABLE roles (id INTEGER PRIMARY KEY, nombre TEXT, descripcion TEXT, activo INTEGER, created_at TEXT, updated_at TEXT)');
        DB::table('roles')->insert([
            ['id' => 1, 'nombre' => 'Administrador General', 'activo' => 1, 'created_at' => '2026-09-06 10:00:00'],
            ['id' => 2, 'nombre' => 'Coordinador', 'activo' => 1, 'created_at' => '2026-09-07 10:00:00'],
            ['id' => 3, 'nombre' => 'Consultor', 'activo' => 0, 'created_at' => '2026-09-08 10:00:00'],
        ]);

        $usuario = new Usuario;
        $usuario->forceFill(['id' => 1, 'nombres' => 'Administrador', 'apellidos' => 'Prueba']);
        $usuario->setRelation('rolRef', new Rol(['nombre' => 'Administrador General']));
        $this->actingAs($usuario);
    }

    public function test_filtra_roles_por_fecha_estado_y_nombre(): void
    {
        $this->getJson('/api/roles?desde=2026-09-07&hasta=2026-09-08')
            ->assertOk()->assertJsonCount(2);

        $this->getJson('/api/roles?desde=2026-09-07&hasta=2026-09-08&activo=1&nombre=Coord')
            ->assertOk()->assertJsonCount(1)->assertJsonPath('0.id', 2);

        $this->getJson('/api/roles?hasta=2026-09-06')
            ->assertOk()->assertJsonCount(1)->assertJsonPath('0.id', 1);
    }

    public function test_rechaza_fecha_invalida(): void
    {
        $this->getJson('/api/roles?desde=2026-99-99')
            ->assertUnprocessable()->assertJsonValidationErrors('desde');
    }
}
