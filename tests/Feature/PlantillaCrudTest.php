<?php

namespace Tests\Feature;

use App\Models\DocumentoGenerado;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\TokenParserService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use RuntimeException;
use Tests\TestCase;

class PlantillaCrudTest extends TestCase
{
    /** @var array<int, string> */
    private array $archivosCreados = [];

    protected function setUp(): void
    {
        parent::setUp();

        DB::unprepared(<<<'SQL'
            CREATE TABLE tipos_documento_plantilla (id INTEGER PRIMARY KEY, codigo TEXT, nombre TEXT, activo INTEGER);
            CREATE TABLE plantillas (id INTEGER PRIMARY KEY AUTOINCREMENT, codigo TEXT, nombre TEXT,
                modulo TEXT, tipo_documento_id INTEGER, familia_id INTEGER, version INTEGER, archivo_path TEXT, sha256 TEXT,
                tokens TEXT, estado TEXT, created_by INTEGER, created_at TEXT, updated_at TEXT, deleted_at TEXT);
            CREATE TABLE plantilla_seleccionada (modulo TEXT, tipo_documento_id INTEGER,
                plantilla_id INTEGER, seleccionado_por INTEGER, seleccionado_at TEXT,
                PRIMARY KEY (modulo, tipo_documento_id));
            CREATE TABLE auditoria (id INTEGER PRIMARY KEY AUTOINCREMENT, expediente_id INTEGER,
                usuario_id INTEGER, accion TEXT, entidad TEXT, entidad_id INTEGER,
                antes TEXT, despues TEXT, ip TEXT, created_at TEXT);
            SQL);
        DB::table('tipos_documento_plantilla')->insert([
            ['id' => 1, 'codigo' => 'CARTA', 'nombre' => 'Carta', 'activo' => 1],
            ['id' => 2, 'codigo' => 'RESOLUCION', 'nombre' => 'Resolución', 'activo' => 0],
        ]);

        $usuario = new Usuario;
        $usuario->forceFill(['id' => 1]);
        $usuario->setRelation('rolRef', new Rol(['nombre' => 'Administrador General']));
        $this->actingAs($usuario);
    }

    protected function tearDown(): void
    {
        foreach ($this->archivosCreados as $ruta) {
            File::delete($ruta);
        }

        parent::tearDown();
    }

    public function test_sube_lista_y_versiona_plantillas(): void
    {
        $this->mock(TokenParserService::class)->shouldReceive('extraer')
            ->times(3)->andReturn(['NOMBRES', 'TOKEN_DESCONOCIDO']);

        $this->getJson('/api/plantillas/tipos')->assertOk()->assertJsonCount(1)
            ->assertJsonPath('0.codigo', 'CARTA');
        $this->postJson('/api/plantillas', [
            'nombre' => 'Tipo inactivo',
            'tipo_documento_id' => 2,
            'archivo' => UploadedFile::fake()->create('plantilla.docx', 10),
        ])->assertUnprocessable()->assertJsonValidationErrors('tipo_documento_id');

        $baseId = null;
        foreach ([1, 2] as $version) {
            $respuesta = $this->postJson('/api/plantillas', [
                'nombre' => 'Carta institucional',
                'tipo_documento_id' => 1,
                'plantilla_base_id' => $baseId,
                'archivo' => UploadedFile::fake()->create('plantilla.docx', 10,
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            ])->assertCreated()->assertJsonPath('version', $version)
                ->assertJsonPath('tokens_count', 2)
                ->assertJsonPath('sin_mapeo.0', 'TOKEN_DESCONOCIDO');

            $id = $respuesta->json('id');
            $baseId ??= $id;
            $this->assertSame('PLA-'.str_pad((string) $id, 4, '0', STR_PAD_LEFT), $respuesta->json('codigo'));
            $ruta = storage_path('app/'.DB::table('plantillas')->where('id', $id)->value('archivo_path'));
            $this->assertFileExists($ruta);
            $this->archivosCreados[] = $ruta;
        }

        $this->deleteJson('/api/plantillas/2')->assertNoContent();
        $tercera = $this->postJson('/api/plantillas', [
            'nombre' => 'Carta institucional',
            'tipo_documento_id' => 1,
            'plantilla_base_id' => $baseId,
            'archivo' => UploadedFile::fake()->create('plantilla.docx', 10,
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ])->assertCreated()->assertJsonPath('version', 3);
        $this->archivosCreados[] = storage_path('app/'.DB::table('plantillas')
            ->where('id', $tercera->json('id'))->value('archivo_path'));

        $this->getJson('/api/plantillas?busqueda=Carta')->assertOk()
            ->assertJsonPath('total', 2)->assertJsonPath('data.0.version', 3)
            ->assertJsonPath('data.0.tokens_count', 2)->assertJsonPath('data.0.vigente', false);
        $this->getJson('/api/plantillas?agrupar=1')->assertOk()
            ->assertJsonPath('total', 1)->assertJsonPath('data.0.version', 3)
            ->assertJsonPath('data.0.familia_id', $baseId);
        $this->getJson('/api/plantillas/'.$baseId.'/versiones')->assertOk()
            ->assertJsonCount(3)->assertJsonPath('0.version', 3)
            ->assertJsonPath('1.eliminada', true);
    }

    public function test_rechaza_archivo_invalido_y_elimina_el_archivo_movido(): void
    {
        $rutaFallida = null;
        $this->mock(TokenParserService::class)->shouldReceive('extraer')
            ->once()->andReturnUsing(function (string $ruta) use (&$rutaFallida): never {
                $rutaFallida = $ruta;
                throw new RuntimeException('DOCX inválido');
            });

        $this->postJson('/api/plantillas', [
            'nombre' => 'Carta inválida',
            'tipo_documento_id' => 1,
            'archivo' => UploadedFile::fake()->create('plantilla.docx', 10,
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ])->assertUnprocessable()->assertJsonValidationErrors('archivo');

        $this->assertNotNull($rutaFallida);
        $this->assertFileDoesNotExist($rutaFallida);
        $this->assertDatabaseCount('plantillas', 0);
    }

    public function test_rechaza_plantilla_sin_campos_y_no_la_permite_seleccionar(): void
    {
        $this->mock(TokenParserService::class)->shouldReceive('extraer')->once()->andReturn([]);

        $this->postJson('/api/plantillas', [
            'nombre' => 'Carta vacía',
            'tipo_documento_id' => 1,
            'archivo' => UploadedFile::fake()->create('vacia.docx', 10),
        ])->assertUnprocessable()->assertJsonValidationErrors('archivo');
        $this->assertDatabaseCount('plantillas', 0);

        DB::table('plantillas')->insert([
            'id' => 1, 'codigo' => 'PLA-0001', 'nombre' => 'Carta antigua',
            'modulo' => 'ARTICULOS', 'tipo_documento_id' => 1,
            'version' => 1, 'estado' => 'ACTIVO', 'tokens' => '[]',
        ]);
        $this->postJson('/api/plantilla-seleccion', [
            'modulo' => 'ARTICULOS', 'tipo_documento_id' => 1, 'plantilla_id' => 1,
        ])->assertUnprocessable()->assertJsonValidationErrors('plantilla_id');
    }

    public function test_versiona_por_familia_aunque_cambie_el_nombre_y_rechaza_campos_perdidos(): void
    {
        $this->mock(TokenParserService::class)->shouldReceive('extraer')->andReturn(
            ['NOMBRES', 'GRADO'],
            ['NOMBRES', 'GRADO'],
            ['NOMBRES'],
            ['NOMBRES', 'GRADO'],
        );
        $datos = [
            'nombre' => 'Carta original',
            'tipo_documento_id' => 1,
            'archivo' => UploadedFile::fake()->create('carta.docx', 10),
        ];
        $primera = $this->postJson('/api/plantillas', $datos)->assertCreated();
        $id = $primera->json('id');
        $this->archivosCreados[] = storage_path('app/'.DB::table('plantillas')->where('id', $id)->value('archivo_path'));

        $this->patchJson('/api/plantillas/'.$id, ['nombre' => 'Carta renombrada'])->assertOk();
        $datos['nombre'] = 'Carta renombrada';
        $datos['archivo'] = UploadedFile::fake()->create('carta.docx', 10);
        $independiente = $this->postJson('/api/plantillas', $datos)->assertCreated()
            ->assertJsonPath('version', 1)->assertJsonPath('nombre', 'Carta renombrada');
        $this->assertNotSame($primera->json('codigo'), $independiente->json('codigo'));
        $this->archivosCreados[] = storage_path('app/'.DB::table('plantillas')->where('id', $independiente->json('id'))->value('archivo_path'));
        $datos['plantilla_base_id'] = $id;
        $datos['archivo'] = UploadedFile::fake()->create('carta.docx', 10);
        $this->postJson('/api/plantillas', $datos)->assertUnprocessable()->assertJsonValidationErrors('archivo');
        $this->assertDatabaseCount('plantillas', 2);

        $datos['archivo'] = UploadedFile::fake()->create('carta.docx', 10);
        $segunda = $this->postJson('/api/plantillas', $datos)->assertCreated()
            ->assertJsonPath('version', 2)->assertJsonPath('familia_id', $id)
            ->assertJsonPath('nombre', 'Carta renombrada');
        $this->archivosCreados[] = storage_path('app/'.DB::table('plantillas')->where('id', $segunda->json('id'))->value('archivo_path'));
    }

    public function test_sube_un_docx_real_y_extrae_sus_tokens(): void
    {
        $rutaTemporal = tempnam(sys_get_temp_dir(), 'plantilla-');
        $documento = new PhpWord;
        $documento->addSection()->addText('Estimado <<NOMBRES>> <<GENERO>>');
        IOFactory::createWriter($documento, 'Word2007')->save($rutaTemporal);
        $zip = new \ZipArchive;
        $zip->open($rutaTemporal);
        $xml = $zip->getFromName('word/document.xml');
        $zip->addFromString('word/document.xml', str_replace(
            ['<<NOMBRES>>', '<<GENERO>>'],
            ['&lt;&lt;NOMBRES&gt;&gt;', '&lt;&lt;GENERO&gt;&gt;'],
            (string) $xml
        ));
        $zip->close();
        $this->assertSame(['GENERO', 'NOMBRES'], app(TokenParserService::class)->extraer($rutaTemporal));

        $respuesta = $this->postJson('/api/plantillas', [
            'nombre' => 'Carta real',
            'tipo_documento_id' => 1,
            'archivo' => new UploadedFile($rutaTemporal, 'carta.docx',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true),
        ]);
        $rutaCreada = DB::table('plantillas')->value('archivo_path');
        if ($rutaCreada !== null) {
            $this->archivosCreados[] = storage_path('app/'.$rutaCreada);
        }

        $respuesta->assertCreated()->assertJsonPath('tokens_count', 2)
            ->assertJsonPath('tokens.0', 'GENERO')->assertJsonPath('tokens.1', 'NOMBRES')->assertJsonPath('sin_mapeo', []);
    }

    public function test_rechaza_docx_con_xml_malformado_sin_dejar_archivos(): void
    {
        $rutaTemporal = tempnam(sys_get_temp_dir(), 'plantilla-');
        $documento = new PhpWord;
        $documento->addSection()->addText('Estimado <<NOMBRES>>');
        IOFactory::createWriter($documento, 'Word2007')->save($rutaTemporal);
        File::ensureDirectoryExists(storage_path('app/plantillas'));
        $archivosAntes = count(File::files(storage_path('app/plantillas')));

        $this->postJson('/api/plantillas', [
            'nombre' => 'Carta corrupta',
            'tipo_documento_id' => 1,
            'archivo' => new UploadedFile($rutaTemporal, 'carta.docx',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true),
        ])->assertUnprocessable()->assertJsonValidationErrors('archivo');

        $this->assertDatabaseCount('plantillas', 0);
        $this->assertCount($archivosAntes, File::files(storage_path('app/plantillas')));
    }

    public function test_estado_baja_y_seleccion_respetan_la_version_vigente(): void
    {
        DB::table('plantillas')->insert([
            ['id' => 1, 'codigo' => 'PLA-0001', 'nombre' => 'Carta', 'modulo' => 'ARTICULOS',
                'tipo_documento_id' => 1, 'familia_id' => 1, 'version' => 1, 'estado' => 'ACTIVO', 'tokens' => '["NOMBRES"]'],
            ['id' => 2, 'codigo' => 'PLA-0002', 'nombre' => 'Carta', 'modulo' => 'ARTICULOS',
                'tipo_documento_id' => 1, 'familia_id' => 1, 'version' => 2, 'estado' => 'INACTIVO', 'tokens' => '["NOMBRES"]'],
        ]);

        $this->postJson('/api/plantilla-seleccion', [
            'modulo' => 'ARTICULOS', 'tipo_documento_id' => 1, 'plantilla_id' => 2,
        ])->assertUnprocessable()->assertJsonValidationErrors('plantilla_id');
        $this->postJson('/api/plantilla-seleccion', [
            'modulo' => 'OTRO', 'tipo_documento_id' => 1, 'plantilla_id' => 1,
        ])->assertUnprocessable()->assertJsonValidationErrors('plantilla_id');
        $this->postJson('/api/plantilla-seleccion', [
            'modulo' => 'ARTICULOS', 'tipo_documento_id' => 1, 'plantilla_id' => 1,
        ])->assertOk()->assertJsonPath('plantilla_id', 1);
        $this->getJson('/api/plantillas')->assertOk()->assertJsonPath('data.1.vigente', true);
        $this->getJson('/api/plantillas?agrupar=1')->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.codigo', 'PLA-0002')
            ->assertJsonPath('data.0.version', 2)
            ->assertJsonPath('data.0.version_vigente', 1);

        $this->patchJson('/api/plantillas/1', ['estado' => 'INACTIVO'])
            ->assertUnprocessable()->assertJsonValidationErrors('estado');
        $this->deleteJson('/api/plantillas/1')->assertUnprocessable()->assertJsonValidationErrors('estado');

        $this->patchJson('/api/plantillas/2', ['estado' => 'ACTIVO'])
            ->assertOk()->assertJsonPath('estado', 'ACTIVO');
        DB::table('plantillas')->where('id', 2)->update(['tokens' => '["TOKEN_DESCONOCIDO"]']);
        $this->postJson('/api/plantilla-seleccion', [
            'modulo' => 'ARTICULOS', 'tipo_documento_id' => 1, 'plantilla_id' => 2,
        ])->assertUnprocessable()->assertJsonValidationErrors('plantilla_id');
        DB::table('plantillas')->where('id', 2)->update(['tokens' => '["NOMBRES"]']);
        $this->postJson('/api/plantilla-seleccion', [
            'modulo' => 'ARTICULOS', 'tipo_documento_id' => 1, 'plantilla_id' => 2,
        ])->assertOk()->assertJsonPath('plantilla_id', 2);
        $this->assertDatabaseCount('plantilla_seleccionada', 1);
        $this->assertDatabaseCount('auditoria', 2);
        $this->deleteJson('/api/plantillas/1')->assertNoContent();
        $this->assertSoftDeleted('plantillas', ['id' => 1, 'estado' => 'INACTIVO']);
        $this->getJson('/api/plantillas?estado=INACTIVO')->assertOk()->assertJsonPath('total', 0);
        $this->patchJson('/api/plantillas/1', ['nombre' => 'Otra carta'])->assertNotFound();
    }

    public function test_edita_nombre_y_conserva_la_referencia_de_documentos_tras_eliminar(): void
    {
        DB::table('plantillas')->insert([
            ['id' => 1, 'codigo' => 'PLA-0001', 'nombre' => 'Carta original', 'modulo' => 'ARTICULOS',
                'tipo_documento_id' => 1, 'version' => 1, 'estado' => 'ACTIVO', 'tokens' => '["NOMBRES"]'],
            ['id' => 2, 'codigo' => 'PLA-0002', 'nombre' => 'Nombre ocupado', 'modulo' => 'ARTICULOS',
                'tipo_documento_id' => 1, 'version' => 1, 'estado' => 'ACTIVO', 'tokens' => '[]'],
        ]);
        DB::statement('CREATE TABLE documentos_generados (id INTEGER PRIMARY KEY, plantilla_id INTEGER)');
        DB::table('documentos_generados')->insert(['id' => 1, 'plantilla_id' => 1]);

        $this->patchJson('/api/plantillas/1', [])->assertUnprocessable()->assertJsonValidationErrors('nombre');
        $this->patchJson('/api/plantillas/1', ['nombre' => 'Nombre ocupado'])
            ->assertOk()->assertJsonPath('nombre', 'Nombre ocupado');
        $this->patchJson('/api/plantillas/1', ['nombre' => 'Carta corregida', 'archivo_path' => 'alterado.docx'])
            ->assertOk()->assertJsonPath('nombre', 'Carta corregida');
        $this->assertDatabaseHas('plantillas', ['id' => 1, 'nombre' => 'Carta corregida', 'archivo_path' => null]);

        $this->deleteJson('/api/plantillas/1')->assertNoContent();
        $this->assertSoftDeleted('plantillas', ['id' => 1]);
        $this->assertSame('Carta corregida', DocumentoGenerado::find(1)->plantilla->nombre);
        $this->getJson('/api/plantillas')->assertOk()->assertJsonPath('total', 1);
    }

    public function test_renombra_plantilla_vigente_con_un_nombre_ya_utilizado(): void
    {
        DB::table('plantillas')->insert([
            ['id' => 1, 'codigo' => 'PLA-0001', 'nombre' => 'Resolución', 'modulo' => 'ARTICULOS',
                'tipo_documento_id' => 1, 'version' => 1, 'estado' => 'ACTIVO', 'tokens' => '["NOMBRES"]'],
            ['id' => 2, 'codigo' => 'PLA-0002', 'nombre' => 'Resolución anterior', 'modulo' => 'ARTICULOS',
                'tipo_documento_id' => 1, 'version' => 1, 'estado' => 'ACTIVO', 'tokens' => '["NOMBRES"]'],
        ]);
        DB::table('plantilla_seleccionada')->insert([
            'modulo' => 'ARTICULOS', 'tipo_documento_id' => 1, 'plantilla_id' => 2,
            'seleccionado_por' => 1, 'seleccionado_at' => now(),
        ]);

        $this->patchJson('/api/plantillas/2', [
            'nombre' => 'Resolución', 'archivo_path' => 'no-debe-cambiar.docx',
        ])->assertOk()->assertJsonPath('id', 2)->assertJsonPath('nombre', 'Resolución');
        $this->assertDatabaseHas('plantillas', ['id' => 1, 'nombre' => 'Resolución']);
        $this->assertDatabaseHas('plantillas', ['id' => 2, 'nombre' => 'Resolución', 'archivo_path' => null]);
        $this->assertDatabaseHas('plantilla_seleccionada', ['plantilla_id' => 2]);
    }

    public function test_un_error_de_auditoria_no_cambia_la_seleccion_vigente(): void
    {
        DB::table('plantillas')->insert([
            ['id' => 1, 'codigo' => 'PLA-0001', 'nombre' => 'Carta', 'modulo' => 'ARTICULOS',
                'tipo_documento_id' => 1, 'version' => 1, 'estado' => 'ACTIVO', 'tokens' => '[]'],
            ['id' => 2, 'codigo' => 'PLA-0002', 'nombre' => 'Carta', 'modulo' => 'ARTICULOS',
                'tipo_documento_id' => 1, 'version' => 2, 'estado' => 'ACTIVO', 'tokens' => '["NOMBRES"]'],
        ]);
        DB::table('plantilla_seleccionada')->insert([
            'modulo' => 'ARTICULOS', 'tipo_documento_id' => 1, 'plantilla_id' => 1,
            'seleccionado_por' => 1, 'seleccionado_at' => now(),
        ]);
        DB::statement('DROP TABLE auditoria');

        $this->postJson('/api/plantilla-seleccion', [
            'modulo' => 'ARTICULOS', 'tipo_documento_id' => 1, 'plantilla_id' => 2,
        ])->assertInternalServerError();

        $this->assertDatabaseHas('plantilla_seleccionada', [
            'modulo' => 'ARTICULOS', 'tipo_documento_id' => 1, 'plantilla_id' => 1,
        ]);
    }

    public function test_solo_administrador_puede_gestionar_plantillas(): void
    {
        $usuario = new Usuario;
        $usuario->forceFill(['id' => 2]);
        $usuario->setRelation('rolRef', new Rol(['nombre' => 'Secretaria']));
        $this->actingAs($usuario);

        $this->getJson('/api/plantillas')->assertForbidden();
        $this->getJson('/api/plantillas/tipos')->assertForbidden();
        $this->postJson('/api/plantillas', [])->assertForbidden();
        $this->postJson('/api/plantilla-seleccion', [])->assertForbidden();
    }
}
