<?php

namespace Tests\Feature;

use App\Events\ExpedienteRegistrado;
use App\Http\Requests\RegistrarExpedienteRequest;
use App\Models\Usuario;
use App\Services\ExpedienteService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RegistroCartaDocenteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::unprepared(<<<'SQL'
            CREATE TABLE docentes (id INTEGER PRIMARY KEY, grado TEXT, tipo_contrato TEXT, escuela_id INTEGER, deleted_at TEXT);
            CREATE TABLE facultades (id INTEGER PRIMARY KEY);
            CREATE TABLE expedientes (id INTEGER PRIMARY KEY AUTOINCREMENT, codigo TEXT, modulo TEXT, docente_id INTEGER,
                grado TEXT, tipo_contrato TEXT, escuela_id INTEGER, carta_docente_numero TEXT,
                carta_docente_registro_numero TEXT, carta_docente_registro_fecha TEXT, carta_docente_fecha TEXT, documentos_completos INTEGER,
                estado TEXT, etapa_actual INTEGER, created_by INTEGER, created_at TEXT, updated_at TEXT, deleted_at TEXT);
            CREATE TABLE expediente_articulos (expediente_id INTEGER PRIMARY KEY, titulo TEXT, revista TEXT,
                base_indexadora TEXT, cuartil TEXT, monto_solicitado NUMERIC, doi TEXT, created_at TEXT, updated_at TEXT);
            SQL);
        DB::table('docentes')->insert(['id' => 1, 'grado' => 'Dr.', 'tipo_contrato' => 'NOMBRADO', 'escuela_id' => 1]);
        DB::table('facultades')->insert(['id' => 1]);
        Event::fake([ExpedienteRegistrado::class]);
    }

    public function test_el_registro_de_carta_es_obligatorio_y_se_guarda_independiente_del_numero_de_carta(): void
    {
        $datos = [
            'carta_docente_numero' => '029-2026-GCHQ',
            'carta_docente_fecha' => '2026-07-22',
            'carta_docente_registro_fecha' => '2026-07-23',
            'docente_id' => 1,
            'facultad_id' => 1,
            'titulo' => 'Artículo de prueba',
            'revista' => 'Revista de prueba',
            'base_indexadora' => 'Scopus',
            'cuartil' => 'Q1',
            'monto_solicitado' => 100,
            'documentos_completos' => true,
        ];

        $reglas = (new RegistrarExpedienteRequest)->rules();
        $this->assertTrue(Validator::make($datos, $reglas)->errors()->has('carta_docente_registro_numero'));

        $datos['carta_docente_registro_numero'] = '1392-2026';
        $this->assertTrue(Validator::make($datos, $reglas)->passes());

        $sinFecha = $datos;
        unset($sinFecha['carta_docente_registro_fecha']);
        $this->assertTrue(Validator::make($sinFecha, $reglas)->errors()->has('carta_docente_registro_fecha'));

        $usuario = new Usuario;
        $usuario->id = 1;
        $resultado = app(ExpedienteService::class)->registrar($datos, $usuario);

        $this->assertSame('1392-2026', $resultado['expediente']->carta_docente_registro_numero);
        $this->assertSame('2026-07-23', $resultado['expediente']->carta_docente_registro_fecha->format('Y-m-d'));
        $this->assertDatabaseHas('expedientes', [
            'carta_docente_numero' => '029-2026-GCHQ',
            'carta_docente_registro_numero' => '1392-2026',
        ]);
    }
}
