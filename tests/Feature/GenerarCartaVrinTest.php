<?php

namespace Tests\Feature;

use App\Jobs\ConvertDocxToPdfJob;
use App\Models\CartaVrin;
use App\Models\DocumentoGenerado;
use App\Models\ExpedienteArticulo;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\ConvertidorPdfService;
use App\Services\PlantillaProcessor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Tests\TestCase;
use ZipArchive;

class GenerarCartaVrinTest extends TestCase
{
    private string $almacenPrueba;

    private string $raizPruebas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->crearEsquema();

        $this->raizPruebas = storage_path('framework/testing');
        $this->almacenPrueba = $this->raizPruebas.'/carta-vrin-'.Str::uuid();
        $this->app->useStoragePath($this->almacenPrueba);
        File::ensureDirectoryExists(storage_path('app/plantillas'));
        config(['logging.default' => 'null']);
        Queue::fake();

        $usuario = new Usuario;
        $usuario->forceFill(['id' => 1, 'nombres' => 'Usuario', 'apellidos' => 'Prueba']);
        $usuario->setRelation('rolRef', new Rol(['nombre' => 'Secretaría']));
        $this->actingAs($usuario);
        $this->crearDatos();
    }

    protected function tearDown(): void
    {
        PlantillaProcessor::fijarDelimitadores('${', '}');
        if (isset($this->almacenPrueba) && is_dir($this->almacenPrueba)) {
            $raiz = realpath($this->raizPruebas).DIRECTORY_SEPARATOR;
            $ruta = realpath($this->almacenPrueba);
            if ($ruta !== false && str_starts_with($ruta, $raiz)) {
                File::deleteDirectory($ruta);
            }
        }
        parent::tearDown();
    }

    public function test_generar_carta_guarda_datos_documento_y_auditoria(): void
    {
        $respuesta = $this->postJson('/api/expedientes/1/carta-vrin', $this->datosCarta());
        $respuesta->assertCreated()
            ->assertJsonPath('estado', 'EN_ESPERA_OPP')
            ->assertJsonPath('etapa_actual', 2)
            ->assertJsonPath('carta_vrin.numero', 67);
        $this->assertDatabaseHas('cartas_vrin', [
            'expediente_id' => 1, 'numero' => 67, 'anio' => 2026,
            'ciudad' => 'Abancay', 'estado' => 'EMITIDA',
            'asunto' => 'Solicito financiamiento para publicación de artículo',
        ]);
        $this->assertSame('2026-10-05', CartaVrin::findOrFail(1)->fecha->format('Y-m-d'));
        $this->assertSame('2026-10-02', ExpedienteArticulo::findOrFail(1)->fecha_aceptacion->format('Y-m-d'));
        $this->assertDatabaseHas('expedientes', [
            'id' => 1, 'registro_mp_numero' => '1392-2026', 'estado' => 'EN_ESPERA_OPP', 'etapa_actual' => 2,
        ]);

        $documento = DocumentoGenerado::findOrFail($respuesta->json('documento_generado.id'));
        $this->assertNotEmpty($documento->codigo_verificacion);
        $this->assertLessThanOrEqual(12, strlen($documento->codigo_verificacion));
        $this->assertSame('CARTA_VRIN', $documento->tipo);
        $this->assertSame('1392-2026', $documento->datos['REGISTRO_MESA_PARTES']);
        $this->assertSame('Solicito financiamiento para publicación de artículo', $documento->datos['ASUNTO_CARTA']);
        $this->assertSame('2 de octubre del 2026', $documento->datos['FECHA_ACEPTACION']);
        $ruta = storage_path('app/'.$documento->docx_path);
        $this->assertFileExists($ruta);
        $this->assertSame(hash_file('sha256', $ruta), $documento->sha256);
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($ruta));
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        $this->assertStringContainsString('067-2026', $xml);
        $this->assertStringContainsString('1392-2026', $xml);
        $this->assertStringContainsString('Solicito financiamiento para publicación de artículo', $xml);
        $this->assertStringContainsString('2 de octubre del 2026', $xml);
        Queue::assertPushed(ConvertDocxToPdfJob::class,
            fn (ConvertDocxToPdfJob $job): bool => $job->documentoGeneradoId === $documento->id);
        $this->assertDatabaseHas('auditoria', ['expediente_id' => 1, 'accion' => 'documento.generado']);
        $this->assertDatabaseHas('auditoria', ['expediente_id' => 1, 'accion' => 'estado.cambiado']);
    }

    public function test_dos_cartas_reciben_codigos_de_verificacion_distintos(): void
    {
        $this->postJson('/api/expedientes/1/carta-vrin', $this->datosCarta())->assertCreated();
        $this->postJson('/api/expedientes/2/carta-vrin', $this->datosCarta(68))->assertCreated();
        $codigos = DocumentoGenerado::pluck('codigo_verificacion');
        $this->assertCount(2, $codigos);
        $this->assertCount(2, $codigos->unique());
    }

    public function test_sin_plantilla_revierte_la_carta_y_el_cambio_de_etapa(): void
    {
        DB::table('plantilla_seleccionada')->delete();
        $this->postJson('/api/expedientes/1/carta-vrin', $this->datosCarta())->assertConflict();
        $this->assertDatabaseCount('cartas_vrin', 0);
        $this->assertDatabaseCount('documentos_generados', 0);
        $this->assertDatabaseHas('expediente_articulos', ['expediente_id' => 1, 'fecha_aceptacion' => null]);
        $this->assertDatabaseHas('expedientes', [
            'id' => 1, 'estado' => 'VALIDADO_CALIDAD', 'etapa_actual' => 1, 'registro_mp_numero' => null,
        ]);
        Queue::assertNothingPushed();
    }

    public function test_preview_convierte_la_plantilla_con_libreoffice_real(): void
    {
        if (! is_file(config('vrin.soffice_path'))) {
            $this->markTestSkipped('LibreOffice no está instalado en este entorno.');
        }
        $this->app->useStoragePath(str_replace(chr(92), '//', $this->almacenPrueba));
        $respuesta = $this->postJson('/api/expedientes/1/carta-vrin/preview', $this->datosCarta());
        $respuesta->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $respuesta->getContent());
        $this->assertDatabaseCount('documentos_generados', 0);
        $this->assertSame([], File::directories(storage_path('app/previews')));
    }

    public function test_preview_utiliza_la_plantilla_y_no_guarda_el_expediente(): void
    {
        $this->mock(ConvertidorPdfService::class, function ($mock): void {
            $mock->shouldReceive('convertir')->once()->andReturnUsing(function (string $docx): string {
                $zip = new ZipArchive;
                $zip->open($docx);
                $xml = $zip->getFromName('word/document.xml');
                $zip->close();
                $this->assertStringContainsString('067-2026', $xml);
                $this->assertStringContainsString('1392-2026', $xml);
                $this->assertStringContainsString($this->datosCarta()['asunto'], $xml);
                $pdf = dirname($docx).'/carta.pdf';
                File::put($pdf, '%PDF-1.4 preview');

                return $pdf;
            });
        });
        $this->postJson('/api/expedientes/1/carta-vrin/preview', $this->datosCarta())
            ->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertDatabaseCount('cartas_vrin', 0);
        $this->assertDatabaseCount('documentos_generados', 0);
        $this->assertDatabaseHas('expedientes', ['id' => 1, 'estado' => 'VALIDADO_CALIDAD', 'registro_mp_numero' => null]);
        $this->assertSame([], File::directories(storage_path('app/previews')));
        Queue::assertNothingPushed();
    }

    public function test_preview_conserva_entorno_del_sistema_cuando_server_solo_contiene_contexto_http(): void
    {
        if (PHP_OS_FAMILY !== 'Windows' || ! is_file(config('vrin.soffice_path'))) {
            $this->markTestSkipped('Requiere LibreOffice en Windows.');
        }

        $serverAnterior = $_SERVER;
        $envAnterior = $_ENV;
        try {
            $_SERVER = ['PATH' => getenv('PATH'), 'REQUEST_METHOD' => 'POST'];
            $_ENV = ['APP_NAME' => 'SGR'];
            $respuesta = $this->postJson('/api/expedientes/1/carta-vrin/preview', $this->datosCarta());
            $respuesta->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $respuesta->getContent());
        } finally {
            $_SERVER = $serverAnterior;
            $_ENV = $envAnterior;
        }
        $this->assertDatabaseCount('documentos_generados', 0);
        $this->assertSame([], File::directories(storage_path('app/previews')));
    }

    public function test_editar_conserva_historial_y_crea_una_unica_version_vigente(): void
    {
        $this->postJson('/api/expedientes/1/carta-vrin', $this->datosCarta())->assertCreated();
        $anterior = DocumentoGenerado::firstOrFail();
        $datos = [...$this->datosCarta(), 'version_actual' => 1, 'asunto' => 'Asunto actualizado'];
        $this->putJson('/api/expedientes/1/carta-vrin', $datos)->assertOk();
        $this->assertDatabaseCount('cartas_vrin', 1);
        $this->assertDatabaseCount('documentos_generados', 2);
        $this->assertFalse($anterior->refresh()->es_vigente);
        $this->assertSame($this->datosCarta()['asunto'], $anterior->datos['ASUNTO_CARTA']);
        $vigente = DocumentoGenerado::where('es_vigente', true)->sole();
        $this->assertEquals(2, $vigente->version);
        $this->assertSame('Asunto actualizado', $vigente->datos['ASUNTO_CARTA']);
        $this->assertFileExists(storage_path('app/'.$anterior->docx_path));
        $this->assertDatabaseHas('expedientes', ['id' => 1, 'estado' => 'EN_ESPERA_OPP', 'etapa_actual' => 2]);
    }

    public function test_edicion_desactualizada_o_numero_duplicado_no_reemplaza_la_carta(): void
    {
        $this->postJson('/api/expedientes/1/carta-vrin', $this->datosCarta())->assertCreated();
        $this->postJson('/api/expedientes/2/carta-vrin', $this->datosCarta(68))->assertCreated();
        $this->putJson('/api/expedientes/1/carta-vrin', [...$this->datosCarta(), 'version_actual' => 2])->assertConflict();
        $this->putJson('/api/expedientes/1/carta-vrin', [...$this->datosCarta(68), 'version_actual' => 1])->assertConflict();
        $this->assertDatabaseCount('documentos_generados', 2);
        $this->assertDatabaseHas('cartas_vrin', ['expediente_id' => 1, 'numero' => 67]);
        $this->assertEquals(2, DocumentoGenerado::where('es_vigente', true)->count());
    }

    public function test_fallo_de_generacion_conserva_la_version_vigente_y_los_datos(): void
    {
        $this->postJson('/api/expedientes/1/carta-vrin', $this->datosCarta())->assertCreated();
        DB::table('plantilla_seleccionada')->delete();
        $this->putJson('/api/expedientes/1/carta-vrin', [...$this->datosCarta(), 'version_actual' => 1, 'asunto' => 'No guardar'])->assertConflict();
        $this->assertDatabaseCount('documentos_generados', 1);
        $this->assertTrue(DocumentoGenerado::sole()->es_vigente);
        $this->assertSame($this->datosCarta()['asunto'], CartaVrin::sole()->asunto);
    }

    public function test_no_permite_editar_despues_de_la_respuesta_opp_ni_a_calidad(): void
    {
        $this->postJson('/api/expedientes/1/carta-vrin', $this->datosCarta())->assertCreated();
        DB::table('expedientes')->where('id', 1)->update(['estado' => 'DISPONIBILIDAD_CONFIRMADA']);
        $this->putJson('/api/expedientes/1/carta-vrin', [...$this->datosCarta(), 'version_actual' => 1])->assertConflict();
        $usuario = new Usuario;
        $usuario->forceFill(['id' => 2]);
        $usuario->setRelation('rolRef', new Rol(['nombre' => 'Calidad']));
        $this->actingAs($usuario);
        $this->putJson('/api/expedientes/1/carta-vrin', [...$this->datosCarta(), 'version_actual' => 1])->assertForbidden();
        $this->assertDatabaseCount('documentos_generados', 1);
    }

    /** @return array<string, int|string> */
    private function datosCarta(int $numero = 67): array
    {
        return [
            'numero' => $numero, 'anio' => 2026, 'fecha' => '2026-10-05',
            'ciudad' => 'Abancay', 'registro_mp_numero' => '1392-2026',
            'asunto' => 'Solicito financiamiento para publicación de artículo', 'fecha_aceptacion' => '2026-10-02',
        ];
    }

    private function crearDatos(): void
    {
        DB::table('docentes')->insert([
            'id' => 1, 'nombres' => 'Docente', 'apellido_paterno' => 'Prueba', 'apellido_materno' => 'Ejemplo',
        ]);
        DB::table('escuelas')->insert(['id' => 1, 'nombre' => 'Escuela de prueba']);
        foreach ([1, 2] as $id) {
            DB::table('expedientes')->insert([
                'id' => $id, 'docente_id' => 1, 'escuela_id' => 1, 'grado' => 'Dr.',
                'carta_docente_numero' => '017-2026', 'carta_docente_fecha' => '2026-10-01',
                'estado' => 'VALIDADO_CALIDAD', 'etapa_actual' => 1, 'documentos_completos' => true,
            ]);
            DB::table('expediente_articulos')->insert([
                'expediente_id' => $id, 'titulo' => 'Artículo de prueba', 'revista' => 'Revista de prueba',
                'base_indexadora' => 'Scopus', 'cuartil' => 'Q1', 'monto_solicitado' => 1500,
            ]);
        }

        $word = new PhpWord;
        $word->addSection()->addText('Carta <<NUMERO_CARTA_VRIN>> / Registro <<REGISTRO_MESA_PARTES>> / <<ASUNTO_CARTA>> / <<FECHA_ACEPTACION>>');
        IOFactory::createWriter($word, 'Word2007')->save(storage_path('app/plantillas/carta.docx'));
        DB::table('tipos_documento_plantilla')->insert(['id' => 1, 'codigo' => 'CARTA']);
        DB::table('plantillas')->insert([
            'id' => 1, 'estado' => 'ACTIVO', 'archivo_path' => 'plantillas/carta.docx',
            'tokens' => json_encode(['NUMERO_CARTA_VRIN', 'REGISTRO_MESA_PARTES', 'ASUNTO_CARTA', 'FECHA_ACEPTACION']),
        ]);
        DB::table('plantilla_seleccionada')->insert([
            'modulo' => 'ARTICULOS', 'tipo_documento_id' => 1, 'plantilla_id' => 1,
        ]);
    }

    /** Esquema mínimo del flujo; conserva NOT NULL y UNIQUE del código exigidos por MySQL. */
    private function crearEsquema(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE TABLE docentes (
                id INTEGER PRIMARY KEY, nombres TEXT, apellido_paterno TEXT, apellido_materno TEXT, deleted_at TEXT
            );
            CREATE TABLE escuelas (id INTEGER PRIMARY KEY, nombre TEXT);
            CREATE TABLE expedientes (
                id INTEGER PRIMARY KEY, docente_id INTEGER, escuela_id INTEGER, grado TEXT,
                carta_docente_numero TEXT, carta_docente_fecha TEXT, registro_mp_numero TEXT,
                estado TEXT, etapa_actual INTEGER, documentos_completos INTEGER,
                created_at TEXT, updated_at TEXT, deleted_at TEXT
            );
            CREATE TABLE expediente_articulos (
                expediente_id INTEGER PRIMARY KEY, titulo TEXT, revista TEXT,
                base_indexadora TEXT, cuartil TEXT, monto_solicitado NUMERIC, fecha_aceptacion TEXT, updated_at TEXT
            );
            CREATE TABLE cartas_vrin (
                expediente_id INTEGER PRIMARY KEY, numero INTEGER, anio INTEGER, fecha TEXT,
                ciudad TEXT, asunto TEXT, estado TEXT, emitida_por INTEGER, created_at TEXT, updated_at TEXT,
                UNIQUE (anio, numero)
            );
            CREATE TABLE tipos_documento_plantilla (id INTEGER PRIMARY KEY, codigo TEXT);
            CREATE TABLE plantillas (id INTEGER PRIMARY KEY, estado TEXT, archivo_path TEXT, tokens TEXT);
            CREATE TABLE plantilla_seleccionada (
                modulo TEXT, tipo_documento_id INTEGER, plantilla_id INTEGER,
                PRIMARY KEY (modulo, tipo_documento_id)
            );
            CREATE TABLE documentos_generados (
                id INTEGER PRIMARY KEY AUTOINCREMENT, expediente_id INTEGER NOT NULL, tipo TEXT NOT NULL,
                version INTEGER NOT NULL, plantilla_id INTEGER NOT NULL, datos TEXT NOT NULL,
                docx_path TEXT NOT NULL, pdf_path TEXT, sha256 TEXT NOT NULL,
                codigo_verificacion VARCHAR(12) NOT NULL UNIQUE CHECK (length(codigo_verificacion) <= 12),
                es_vigente INTEGER NOT NULL, generado_por INTEGER NOT NULL, generado_at TEXT NOT NULL,
                created_at TEXT, UNIQUE (expediente_id, tipo, version)
            );
            CREATE TABLE auditoria (
                id INTEGER PRIMARY KEY AUTOINCREMENT, expediente_id INTEGER, usuario_id INTEGER,
                accion TEXT, entidad TEXT, entidad_id INTEGER, antes TEXT, despues TEXT, ip TEXT, created_at TEXT
            );
            SQL);
    }
}
