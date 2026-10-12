<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardResumenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        DB::unprepared(<<<'SQL'
            CREATE TABLE expedientes (id INTEGER PRIMARY KEY, codigo TEXT, docente_id INTEGER,
                estado TEXT, created_at TEXT, updated_at TEXT, deleted_at TEXT);
            CREATE TABLE rendiciones (expediente_id INTEGER PRIMARY KEY, fecha_desembolso TEXT,
                monto_desembolsado NUMERIC);
            CREATE TABLE docentes (id INTEGER PRIMARY KEY, activo INTEGER, deleted_at TEXT);
            CREATE TABLE plantillas (id INTEGER PRIMARY KEY, updated_at TEXT);
            SQL);

        DB::table('expedientes')->insert([
            ['id' => 1, 'codigo' => 'ART-2026-000001', 'estado' => 'RENDIDO',
                'created_at' => '2026-10-09 12:00:00', 'updated_at' => '2026-10-09 12:00:00', 'deleted_at' => null],
            ['id' => 2, 'codigo' => 'ART-2026-000002', 'estado' => 'RENDIDO',
                'created_at' => '2026-10-10 12:00:00', 'updated_at' => '2026-10-10 12:00:00', 'deleted_at' => null],
            ['id' => 3, 'codigo' => 'ART-2026-000003', 'estado' => 'RENDIDO',
                'created_at' => '2026-10-07 12:00:00', 'updated_at' => '2026-10-07 12:00:00',
                'deleted_at' => '2026-10-09 12:00:00'],
            ['id' => 4, 'codigo' => 'ART-2025-000001', 'estado' => 'RENDIDO',
                'created_at' => '2025-10-09 12:00:00', 'updated_at' => '2025-10-09 12:00:00', 'deleted_at' => null],
        ]);

        DB::table('rendiciones')->insert([
            ['expediente_id' => 1, 'fecha_desembolso' => '2026-10-09', 'monto_desembolsado' => 11420],
            ['expediente_id' => 2, 'fecha_desembolso' => '2026-10-10', 'monto_desembolsado' => 9883],
            ['expediente_id' => 3, 'fecha_desembolso' => '2026-10-07', 'monto_desembolsado' => 9883],
            ['expediente_id' => 4, 'fecha_desembolso' => '2025-10-09', 'monto_desembolsado' => 25.50],
        ]);

        $usuario = new Usuario;
        $usuario->forceFill(['id' => 1, 'nombres' => 'Administrador', 'apellidos' => 'Prueba']);
        $usuario->setRelation('rolRef', new Rol(['nombre' => 'Administrador General']));
        $this->actingAs($usuario);
    }

    public function test_monto_financiado_excluye_expedientes_eliminados_y_respeta_el_anio(): void
    {
        $this->getJson('/api/panel-control?year=2026')->assertOk()
            ->assertJsonPath('monto_financiado', 21303)
            ->assertJsonPath('expedientes', 3);

        $this->getJson('/api/panel-control?year=2025')->assertOk()
            ->assertJsonPath('monto_financiado', 25.50);
    }
}
