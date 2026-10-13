<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ListaUsuariosTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::connection()->getDriverName());
        DB::unprepared(<<<'SQL'
            CREATE TABLE roles (id INTEGER PRIMARY KEY, nombre TEXT, activo INTEGER);
            CREATE TABLE usuarios (id INTEGER PRIMARY KEY, nombres TEXT, apellidos TEXT, dni TEXT,
                email TEXT, rol_id INTEGER, activo INTEGER, created_at TEXT, deleted_at TEXT,
                ultimo_login_at TEXT);
            SQL);
        DB::table('roles')->insert(['id' => 1, 'nombre' => 'Administrador General', 'activo' => 1]);
        DB::table('usuarios')->insert([
            ['id' => 1, 'nombres' => 'Ana', 'apellidos' => 'Quispe', 'dni' => '12345678',
                'email' => 'ana@example.test', 'rol_id' => 1, 'activo' => 1,
                'created_at' => '2026-09-06 10:00:00', 'deleted_at' => null],
            ['id' => 2, 'nombres' => 'Luis', 'apellidos' => 'Flores', 'dni' => '87654321',
                'email' => 'luis@example.test', 'rol_id' => 1, 'activo' => 0,
                'created_at' => '2026-09-07 10:00:00', 'deleted_at' => null],
            ['id' => 3, 'nombres' => 'Julia', 'apellidos' => 'Rojas', 'dni' => '11112222',
                'email' => 'julia@example.test', 'rol_id' => 1, 'activo' => 1,
                'created_at' => '2026-09-08 10:00:00', 'deleted_at' => '2026-09-09 10:00:00'],
        ]);

        $admin = new Usuario;
        $admin->forceFill(['id' => 99, 'nombres' => 'Administrador', 'apellidos' => 'Prueba']);
        $admin->setRelation('rolRef', new Rol(['nombre' => 'Administrador General']));
        $this->actingAs($admin);
    }

    public function test_busca_nombre_correo_y_dni_y_combina_estado_con_fechas(): void
    {
        $this->getJson('/api/usuarios?busqueda=Ana%20Quispe')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 1);
        $this->getJson('/api/usuarios?busqueda=example.test')
            ->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/usuarios?busqueda=87654321')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 2);

        $this->getJson('/api/usuarios?busqueda=87654321&activo=0&desde=2026-09-07&hasta=2026-09-07')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 2);

        $this->getJson('/api/usuarios?hasta=2026-09-06')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 1);

        $this->getJson('/api/usuarios')->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('total', 2)->assertJsonPath('per_page', 15);
    }

    public function test_rechaza_fecha_invalida(): void
    {
        $this->getJson('/api/usuarios?desde=2026-99-99')
            ->assertUnprocessable()->assertJsonValidationErrors('desde');
    }
}
