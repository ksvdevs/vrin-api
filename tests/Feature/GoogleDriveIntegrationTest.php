<?php

namespace Tests\Feature;

use App\Models\DocumentoGenerado;
use App\Models\GoogleDriveConnection;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class GoogleDriveIntegrationTest extends TestCase
{
    private string $almacenPrueba;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        DB::unprepared(<<<'SQL'
            CREATE TABLE expedientes (id INTEGER PRIMARY KEY, deleted_at TEXT);
            CREATE TABLE documentos_generados (id INTEGER PRIMARY KEY, expediente_id INTEGER, tipo TEXT,
                version INTEGER, docx_path TEXT, sha256 TEXT, datos TEXT, es_vigente INTEGER,
                drive_file_id TEXT,
                drive_folder_id TEXT, drive_synced_at TEXT, generated_at TEXT, created_at TEXT);
            CREATE TABLE google_drive_connections (id INTEGER PRIMARY KEY, refresh_token TEXT,
                connected_at TEXT, created_at TEXT, updated_at TEXT);
            SQL);
        config([
            'cache.default' => 'array',
            'services.google.client_id' => 'cliente-prueba',
            'services.google.client_secret' => 'secreto-prueba',
            'services.google_drive.redirect' => 'https://api.example.test/api/drive/oauth/callback',
            'services.google_drive.folder_id' => 'carpeta-prueba',
            'services.google_drive.resolution_folder_id' => 'carpeta-resoluciones',
            'services.google_drive.account_email' => 'ksegundo@unamba.edu.pe',
        ]);

        $this->almacenPrueba = storage_path('framework/testing/google-drive-'.Str::uuid());
        $this->app->useStoragePath($this->almacenPrueba);
        File::ensureDirectoryExists(storage_path('app/documentos/9'));

        $usuario = new Usuario;
        $usuario->forceFill(['id' => 1]);
        $usuario->setRelation('rolRef', new Rol(['nombre' => 'Administrador General']));
        $this->actingAs($usuario);
        DB::table('expedientes')->insert(['id' => 9]);
    }

    protected function tearDown(): void
    {
        if (isset($this->almacenPrueba) && is_dir($this->almacenPrueba)) {
            File::deleteDirectory($this->almacenPrueba);
        }
        parent::tearDown();
    }

    public function test_el_administrador_conecta_la_cuenta_con_permiso_para_la_carpeta(): void
    {
        $url = $this->postJson('/api/drive/authorize')->assertOk()->json('authorization_url');
        parse_str(parse_url($url, PHP_URL_QUERY), $parametros);
        $this->assertSame('https://www.googleapis.com/auth/drive openid email', $parametros['scope']);
        $this->assertSame('offline', $parametros['access_type']);
        $this->assertSame('ksegundo@unamba.edu.pe', $parametros['login_hint']);
        $this->assertSame('https://api.example.test/api/drive/oauth/callback', $parametros['redirect_uri']);
        $this->assertTrue(Cache::has('google_drive_oauth_'.$parametros['state']));

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'acceso-prueba', 'refresh_token' => 'renovacion-secreta',
            ]),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'email' => 'ksegundo@unamba.edu.pe', 'email_verified' => true,
            ]),
            'https://www.googleapis.com/drive/v3/files/carpeta-prueba*' => Http::response([
                'id' => 'carpeta-prueba', 'mimeType' => 'application/vnd.google-apps.folder',
                'capabilities' => ['canAddChildren' => true],
            ]),
            'https://www.googleapis.com/drive/v3/files/carpeta-resoluciones*' => Http::response([
                'id' => 'carpeta-resoluciones', 'mimeType' => 'application/vnd.google-apps.folder',
                'capabilities' => ['canAddChildren' => true],
            ]),
        ]);

        $this->get('/api/drive/oauth/callback?'.http_build_query([
            'state' => $parametros['state'], 'code' => 'codigo-prueba',
        ]))->assertOk();
        $this->getJson('/api/drive/status')->assertOk()->assertJsonPath('connected', true);
        $this->getJson('/api/drive/status')->assertOk()->assertJsonPath(
            'resolution_folder_url', 'https://drive.google.com/drive/folders/carpeta-resoluciones'
        );
        $this->assertSame('renovacion-secreta', GoogleDriveConnection::findOrFail(1)->refresh_token);
        $this->assertNotSame('renovacion-secreta', DB::table('google_drive_connections')->value('refresh_token'));
        $this->get('/api/drive/oauth/callback?'.http_build_query([
            'state' => $parametros['state'], 'code' => 'codigo-prueba',
        ]))->assertBadRequest();
    }

    public function test_no_conecta_una_cuenta_sin_permiso_para_agregar_archivos(): void
    {
        $url = $this->postJson('/api/drive/authorize')->assertOk()->json('authorization_url');
        parse_str(parse_url($url, PHP_URL_QUERY), $parametros);
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'acceso-prueba', 'refresh_token' => 'renovacion-secreta',
            ]),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'email' => 'ksegundo@unamba.edu.pe', 'email_verified' => true,
            ]),
            'https://www.googleapis.com/drive/v3/files/carpeta-prueba*' => Http::response([
                'id' => 'carpeta-prueba', 'mimeType' => 'application/vnd.google-apps.folder',
                'capabilities' => ['canAddChildren' => false],
            ]),
        ]);

        $this->get('/api/drive/oauth/callback?'.http_build_query([
            'state' => $parametros['state'], 'code' => 'codigo-prueba',
        ]))->assertForbidden();
        $this->assertDatabaseCount('google_drive_connections', 0);
    }

    public function test_no_conecta_una_cuenta_distinta_a_la_institucional_configurada(): void
    {
        $url = $this->postJson('/api/drive/authorize')->assertOk()->json('authorization_url');
        parse_str(parse_url($url, PHP_URL_QUERY), $parametros);
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'acceso-prueba', 'refresh_token' => 'renovacion-secreta',
            ]),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'email' => 'otra-cuenta@gmail.com', 'email_verified' => true,
            ]),
        ]);

        $this->get('/api/drive/oauth/callback?'.http_build_query([
            'state' => $parametros['state'], 'code' => 'codigo-prueba',
        ]))->assertForbidden();
        $this->assertDatabaseCount('google_drive_connections', 0);
    }

    public function test_no_conecta_si_no_puede_escribir_en_la_carpeta_de_resoluciones(): void
    {
        $url = $this->postJson('/api/drive/authorize')->assertOk()->json('authorization_url');
        parse_str(parse_url($url, PHP_URL_QUERY), $parametros);
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'acceso-prueba', 'refresh_token' => 'renovacion-secreta',
            ]),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'email' => 'ksegundo@unamba.edu.pe', 'email_verified' => true,
            ]),
            'https://www.googleapis.com/drive/v3/files/carpeta-prueba*' => Http::response([
                'mimeType' => 'application/vnd.google-apps.folder', 'capabilities' => ['canAddChildren' => true],
            ]),
            'https://www.googleapis.com/drive/v3/files/carpeta-resoluciones*' => Http::response([
                'mimeType' => 'application/vnd.google-apps.folder', 'capabilities' => ['canAddChildren' => false],
            ]),
        ]);

        $this->get('/api/drive/oauth/callback?'.http_build_query([
            'state' => $parametros['state'], 'code' => 'codigo-prueba',
        ]))->assertForbidden();
        $this->assertDatabaseCount('google_drive_connections', 0);
    }

    public function test_subir_carta_guarda_el_nombre_y_evitar_copias_al_repetir_la_descarga(): void
    {
        GoogleDriveConnection::create([
            'id' => 1, 'refresh_token' => 'renovacion-secreta', 'connected_at' => now(),
        ]);
        $ruta = storage_path('app/documentos/9/carta.docx');
        File::put($ruta, 'contenido de la carta');
        $documento = DocumentoGenerado::create([
            'expediente_id' => 9, 'tipo' => 'CARTA_VRIN', 'version' => 2,
            'docx_path' => 'documentos/9/carta.docx', 'sha256' => hash_file('sha256', $ruta),
            'datos' => ['NUMERO_CARTA_VRIN' => '791-2026'], 'es_vigente' => true,
        ]);
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'acceso-prueba']),
            'https://www.googleapis.com/upload/drive/v3/files*' => Http::response(['id' => 'archivo-drive-123']),
        ]);

        $rutaApi = "/api/expedientes/9/documentos/{$documento->id}/drive";
        $this->postJson($rutaApi)->assertOk()
            ->assertJsonPath('id', 'archivo-drive-123')
            ->assertJsonPath('already_synced', false);
        $this->postJson($rutaApi)->assertOk()->assertJsonPath('already_synced', true);
        $this->assertDatabaseHas('documentos_generados', [
            'id' => $documento->id, 'drive_file_id' => 'archivo-drive-123', 'drive_folder_id' => 'carpeta-prueba',
        ]);
        Http::assertSentCount(2);
        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/upload/drive/v3/files')
            && str_contains($request->body(), 'CARTA_VRIN_791-2026_v2.docx')
            && str_contains($request->body(), '"parents":["carpeta-prueba"]'));
    }

    public function test_descarga_sin_cuenta_conectada_devuelve_un_error_claro(): void
    {
        $ruta = storage_path('app/documentos/9/carta.docx');
        File::put($ruta, 'contenido de la carta');
        $documento = DocumentoGenerado::create([
            'expediente_id' => 9, 'tipo' => 'CARTA_VRIN', 'version' => 2,
            'docx_path' => 'documentos/9/carta.docx', 'sha256' => hash_file('sha256', $ruta),
            'datos' => ['NUMERO_CARTA_VRIN' => '791-2026'], 'es_vigente' => true,
        ]);

        $this->postJson("/api/expedientes/9/documentos/{$documento->id}/drive")
            ->assertStatus(503)
            ->assertJsonPath('message', 'Conecta la cuenta institucional de Google Drive antes de descargar la carta.');
    }

    public function test_subir_resolucion_usa_su_carpeta_su_numero_y_no_crea_duplicados(): void
    {
        GoogleDriveConnection::create([
            'id' => 1, 'refresh_token' => 'renovacion-secreta', 'connected_at' => now(),
        ]);
        $ruta = storage_path('app/documentos/9/resolucion.docx');
        File::put($ruta, 'contenido de la resolucion');
        $documento = DocumentoGenerado::create([
            'expediente_id' => 9, 'tipo' => 'RESOLUCION', 'version' => 2,
            'docx_path' => 'documentos/9/resolucion.docx', 'sha256' => hash_file('sha256', $ruta),
            'datos' => ['numero_resolucion' => '013', 'anio' => 2026], 'es_vigente' => true,
        ]);
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'acceso-prueba']),
            'https://www.googleapis.com/upload/drive/v3/files*' => Http::response(['id' => 'resolucion-drive-123']),
        ]);

        $rutaApi = "/api/expedientes/9/documentos/{$documento->id}/drive";
        $this->postJson($rutaApi)->assertOk()->assertJsonPath('already_synced', false);
        $this->postJson($rutaApi)->assertOk()->assertJsonPath('already_synced', true);
        $this->assertDatabaseHas('documentos_generados', [
            'id' => $documento->id,
            'drive_file_id' => 'resolucion-drive-123',
            'drive_folder_id' => 'carpeta-resoluciones',
        ]);
        Http::assertSentCount(2);
        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/upload/drive/v3/files')
            && str_contains($request->body(), 'RESOLUCION_013-2026_v2.docx')
            && str_contains($request->body(), '"parents":["carpeta-resoluciones"]'));
    }
}
