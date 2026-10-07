<?php

namespace Tests\Feature;

use App\Events\EstadoCambiado;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class RendicionPaso4Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        DB::unprepared(<<<'SQL'
            CREATE TABLE expedientes (id INTEGER PRIMARY KEY, estado TEXT, etapa_actual INTEGER,
                cerrado_at TEXT, created_at TEXT, updated_at TEXT, deleted_at TEXT);
            CREATE TABLE archivos (id INTEGER PRIMARY KEY, expediente_id INTEGER, etapa INTEGER,
                tipo TEXT, subido_por INTEGER, nombre_original TEXT, storage_path TEXT,
                mime TEXT, tamano_bytes INTEGER, sha256 TEXT, deleted_at TEXT, created_at TEXT);
            CREATE TABLE rendiciones (expediente_id INTEGER PRIMARY KEY, fecha_desembolso TEXT,
                monto_desembolsado NUMERIC, fecha_limite TEXT, fecha_informe TEXT,
                estado TEXT CHECK (estado IN ('BORRADOR', 'CERRADA')),
                con_retraso INTEGER, cerrada_por INTEGER, cerrada_at TEXT,
                created_at TEXT, updated_at TEXT);
            SQL);
        DB::table('expedientes')->insert([
            ['id' => 1, 'estado' => 'POR_RENDIR'],
            ['id' => 2, 'estado' => 'RENDIDO'],
        ]);
        DB::table('archivos')->insert([
            ['id' => 10, 'expediente_id' => 1, 'tipo' => 'COMPROBANTE_RENDICION'],
            ['id' => 11, 'expediente_id' => 1, 'tipo' => 'CARTA_DOCENTE'],
            ['id' => 12, 'expediente_id' => 2, 'tipo' => 'COMPROBANTE_RENDICION'],
        ]);

        $usuario = new Usuario;
        $usuario->forceFill(['id' => 1, 'nombres' => 'Usuario', 'apellidos' => 'Prueba']);
        $usuario->setRelation('rolRef', new Rol(['nombre' => 'Administrador General']));
        $this->actingAs($usuario);
    }

    public function test_retirar_comprobante_usa_baja_logica_y_solo_permite_rendiciones_abiertas(): void
    {
        $this->deleteJson('/api/expedientes/1/archivos/10')->assertNoContent();
        $this->assertNotNull(DB::table('archivos')->where('id', 10)->value('deleted_at'));

        $this->deleteJson('/api/expedientes/1/archivos/11')->assertNotFound();
        $this->assertNull(DB::table('archivos')->where('id', 11)->value('deleted_at'));

        $this->deleteJson('/api/expedientes/2/archivos/12')->assertStatus(409);
        $this->assertNull(DB::table('archivos')->where('id', 12)->value('deleted_at'));
    }

    public function test_desembolso_exige_un_monto_positivo(): void
    {
        DB::table('expedientes')->where('id', 1)->update(['estado' => 'RESOLUCION_EMITIDA']);

        $this->postJson('/api/expedientes/1/rendicion/desembolso', [
            'fecha_desembolso' => '2026-10-07',
        ])->assertUnprocessable()->assertJsonValidationErrors('monto_desembolsado');

        $this->postJson('/api/expedientes/1/rendicion/desembolso', [
            'fecha_desembolso' => '2026-10-07',
            'monto_desembolsado' => 0,
        ])->assertUnprocessable()->assertJsonValidationErrors('monto_desembolsado');
    }

    public function test_comprobante_debe_ser_pdf_y_requiere_desembolso_registrado(): void
    {
        $this->postJson('/api/expedientes/1/archivos', [
            'tipo' => 'COMPROBANTE_RENDICION',
            'archivo' => UploadedFile::fake()->create('comprobante.docx', 10),
        ])->assertUnprocessable()->assertJsonValidationErrors('archivo');

        DB::table('expedientes')->where('id', 1)->update(['estado' => 'RESOLUCION_EMITIDA']);
        $this->postJson('/api/expedientes/1/archivos', [
            'tipo' => 'COMPROBANTE_RENDICION',
            'archivo' => UploadedFile::fake()->create('comprobante.pdf', 10, 'application/pdf'),
        ])->assertStatus(409);
    }

    public function test_registra_desembolso_y_permite_cerrar_la_rendicion(): void
    {
        Event::fake([EstadoCambiado::class]);
        DB::table('expedientes')->where('id', 1)->update([
            'estado' => 'RESOLUCION_EMITIDA', 'etapa_actual' => 3,
        ]);

        $this->postJson('/api/expedientes/1/rendicion/desembolso', [
            'fecha_desembolso' => '2026-10-07',
            'monto_desembolsado' => 1500.75,
        ])->assertCreated()->assertJsonPath('estado', 'POR_RENDIR');
        $this->assertDatabaseHas('rendiciones', [
            'expediente_id' => 1,
            'monto_desembolsado' => 1500.75,
            'estado' => 'BORRADOR',
        ]);

        $this->postJson('/api/expedientes/1/rendicion/cerrar', [
            'fecha_informe' => '2026-10-06',
        ])->assertUnprocessable()->assertJsonValidationErrors('fecha_informe');

        $this->postJson('/api/expedientes/1/rendicion/cerrar', [
            'fecha_informe' => '2027-01-07',
        ])->assertOk()->assertJsonPath('estado', 'RENDIDO')
            ->assertJsonPath('rendicion.estado', 'CERRADA');
        $this->assertDatabaseHas('rendiciones', [
            'expediente_id' => 1,
            'estado' => 'CERRADA',
        ]);
        $this->postJson('/api/expedientes/1/rendicion/cerrar', [
            'fecha_informe' => '2027-01-07',
        ])->assertStatus(409);
    }

    public function test_sube_visualiza_y_retira_un_comprobante_sin_borrar_el_pdf_fisico(): void
    {
        $raiz = storage_path('framework/testing');
        $almacen = $raiz.'/rendicion-'.Str::uuid();
        File::ensureDirectoryExists($almacen);
        $this->app->useStoragePath($almacen);

        try {
            $respuesta = $this->postJson('/api/expedientes/1/archivos', [
                'tipo' => 'COMPROBANTE_RENDICION',
                'etapa' => 4,
                'archivo' => UploadedFile::fake()->create('pago.pdf', 10, 'application/pdf'),
            ])->assertCreated()->assertJsonPath('tipo', 'COMPROBANTE_RENDICION');

            $id = $respuesta->json('id');
            $ruta = DB::table('archivos')->where('id', $id)->value('storage_path');
            $this->assertFileExists(storage_path('app/'.$ruta));
            $this->get('/api/expedientes/1/archivos/'.$id)
                ->assertOk()->assertHeader('Content-Type', 'application/pdf');

            $this->deleteJson('/api/expedientes/1/archivos/'.$id)->assertNoContent();
            $this->get('/api/expedientes/1/archivos/'.$id)->assertNotFound();
            $this->assertFileExists(storage_path('app/'.$ruta));
        } finally {
            $rutaBase = realpath($raiz);
            $rutaAlmacen = realpath($almacen);
            if ($rutaBase !== false && $rutaAlmacen !== false
                && str_starts_with($rutaAlmacen, $rutaBase.DIRECTORY_SEPARATOR)) {
                File::deleteDirectory($rutaAlmacen);
            }
        }
    }
}
