<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Expediente;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class RegistroUsuarioCorreoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::unprepared(<<<'SQL'
            CREATE TABLE roles (id INTEGER PRIMARY KEY, nombre TEXT, activo INTEGER);
            CREATE TABLE usuarios (id INTEGER PRIMARY KEY, nombres TEXT, apellidos TEXT, dni TEXT UNIQUE,
                email TEXT UNIQUE, rol_id INTEGER, password_hash TEXT, google_sub TEXT,
                activo INTEGER, created_at TEXT, updated_at TEXT, deleted_at TEXT, ultimo_login_at TEXT);
            CREATE TABLE actores_historicos (id INTEGER PRIMARY KEY, nombres TEXT, apellidos TEXT,
                rol_nombre TEXT, fecha_alta TEXT, archivado_at TEXT);
            SQL);
        DB::table('roles')->insert(['id' => 1, 'nombre' => 'Administrador General', 'activo' => 1]);

        $admin = new Usuario;
        $admin->forceFill(['id' => 99]);
        $admin->setRelation('rolRef', new Rol(['nombre' => 'Administrador General']));
        $this->actingAs($admin);
    }

    public function test_genera_y_envia_una_contrasena_que_permite_iniciar_sesion(): void
    {
        $claveEnviada = null;
        Mail::shouldReceive('send')->once()->withArgs(function (string $vista, array $datos, callable $configurar) use (&$claveEnviada): bool {
            $claveEnviada = $datos['password'];
            $html = view($vista, $datos)->render();

            $this->assertStringContainsString('#3765a0', $html);
            $this->assertStringContainsString('nuevo@unamba.edu.pe', $html);
            $this->assertStringContainsString($datos['loginUrl'], $html);

            return $vista === 'emails.usuario-registrado';
        });

        $this->postJson('/api/usuarios', $this->datos())->assertCreated()->assertJsonMissingPath('password_hash');

        $usuario = Usuario::where('email', 'nuevo@unamba.edu.pe')->firstOrFail();
        $this->assertDatabaseHas('actores_historicos', [
            'id' => $usuario->id, 'nombres' => 'Nuevo', 'rol_nombre' => 'Administrador General',
        ]);
        $this->assertNotNull($claveEnviada);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $claveEnviada);
        $this->assertTrue(Hash::check($claveEnviada, $usuario->password_hash));

        $this->withHeader('Origin', 'http://localhost:4200')->postJson('/api/login', [
            'email' => $usuario->email,
            'password' => $claveEnviada,
        ])->assertOk()->assertJsonPath('id', $usuario->id);
    }

    public function test_no_registra_al_usuario_si_falla_el_envio(): void
    {
        Mail::shouldReceive('send')->once()->andThrow(new TransportException('SMTP no disponible'));

        $this->postJson('/api/usuarios', $this->datos())
            ->assertStatus(503)
            ->assertJsonPath('message', 'No se pudo enviar la contraseña por correo. Revisa las credenciales SMTP; el usuario no fue registrado.');

        $this->assertDatabaseMissing('usuarios', ['email' => 'nuevo@unamba.edu.pe']);
    }

    public function test_rechaza_una_contrasena_enviada_por_el_cliente(): void
    {
        $this->postJson('/api/usuarios', [...$this->datos(), 'password' => 'clave-elegida'])
            ->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_rechaza_un_dni_con_letras_sin_registrar_al_usuario(): void
    {
        $this->postJson('/api/usuarios', [...$this->datos(), 'dni' => '12ab5678'])
            ->assertUnprocessable()->assertJsonValidationErrors('dni');

        $this->assertDatabaseCount('usuarios', 0);
    }

    public function test_google_no_crea_usuarios_sin_registro_previo(): void
    {
        $googleUser = new GoogleUser;
        $googleUser->email = 'desconocido@unamba.edu.pe';
        $googleUser->id = 'google-123';
        $googleUser->user = ['email_verified' => true];

        $proveedor = \Mockery::mock();
        $proveedor->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($proveedor);

        $this->get('/api/auth/google/callback')
            ->assertRedirect(config('app.frontend_url').'/login?error=acceso_denegado');

        $this->assertDatabaseCount('usuarios', 0);
    }

    public function test_google_usa_la_uri_configurada_y_permita_elegir_cuenta(): void
    {
        config([
            'services.google.client_id' => 'cliente-prueba',
            'services.google.client_secret' => 'secreto-prueba',
            'services.google.redirect' => 'http://127.0.0.1:8000/api/auth/google/callback',
        ]);

        $respuesta = $this->get('/api/auth/google/redirect');
        $respuesta->assertRedirect();
        parse_str((string) parse_url($respuesta->headers->get('Location'), PHP_URL_QUERY), $parametros);

        $this->assertSame('http://127.0.0.1:8000/api/auth/google/callback', $parametros['redirect_uri']);
        $this->assertSame('select_account', $parametros['prompt']);
    }

    public function test_google_inicia_sesion_si_el_correo_esta_registrado_y_verificado(): void
    {
        DB::table('usuarios')->insert([
            'id' => 1, 'dni' => '12345678', 'nombres' => 'Nuevo', 'apellidos' => 'Usuario',
            'email' => 'nuevo@unamba.edu.pe', 'rol_id' => 1, 'activo' => 1,
        ]);
        $googleUser = new GoogleUser;
        $googleUser->email = 'nuevo@unamba.edu.pe';
        $googleUser->id = 'google-123';
        $googleUser->user = ['email_verified' => true];

        $proveedor = \Mockery::mock();
        $proveedor->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($proveedor);

        $auditoria = \Mockery::mock(AuditoriaService::class);
        $auditoria->shouldReceive('registrar')->once()->andReturn(new Auditoria);
        $this->app->instance(AuditoriaService::class, $auditoria);

        $this->get('/api/auth/google/callback')
            ->assertRedirect(config('app.frontend_url').'/');

        $this->assertDatabaseHas('usuarios', ['id' => 1, 'google_sub' => 'google-123']);
        $this->assertSame(1, Auth::guard('web')->id());
    }

    public function test_consulta_dni_y_devuelve_nombres_y_apellidos_para_el_formulario(): void
    {
        config(['services.apisperu.token' => 'token-prueba']);
        Http::fake(['dniruc.apisperu.com/*' => Http::response([
            'success' => true,
            'nombres' => 'María Elena',
            'apellidoPaterno' => 'Quispe',
            'apellidoMaterno' => 'Rojas',
        ])]);

        $this->getJson('/api/usuarios/consultar-dni?dni=77100148')
            ->assertOk()
            ->assertJsonPath('registrado', false)
            ->assertJsonPath('datos.nombres', 'María Elena')
            ->assertJsonPath('datos.apellidos', 'Quispe Rojas');

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/api/v1/dni/77100148')
            && str_contains($request->url(), 'token=token-prueba'));
    }

    public function test_dni_ya_registrado_no_consulta_la_api_externa(): void
    {
        DB::table('usuarios')->insert([
            'dni' => '77100148', 'nombres' => 'María', 'apellidos' => 'Quispe',
            'email' => 'maria@unamba.edu.pe', 'rol_id' => 1, 'activo' => 1,
        ]);
        Http::fake();

        $this->getJson('/api/usuarios/consultar-dni?dni=77100148')
            ->assertOk()->assertJsonPath('registrado', true);

        Http::assertNothingSent();
    }

    public function test_reactiva_usuario_eliminado_anteriormente_con_el_mismo_id_y_nueva_contrasena(): void
    {
        DB::table('usuarios')->insert([
            'id' => 1, 'dni' => '12345678', 'nombres' => 'Anterior', 'apellidos' => 'Usuario',
            'email' => 'anterior@unamba.edu.pe', 'rol_id' => 1,
            'password_hash' => Hash::make('clave-anterior'), 'google_sub' => 'google-anterior', 'activo' => 1,
        ]);

        DB::table('usuarios')->where('id', 1)->update(['deleted_at' => now()]);
        $this->assertNotNull(DB::table('usuarios')->where('id', 1)->value('deleted_at'));

        config(['services.apisperu.token' => 'token-prueba']);
        Http::fake(['dniruc.apisperu.com/*' => Http::response([
            'success' => true, 'nombres' => 'Nuevo', 'apellidoPaterno' => 'Usuario',
        ])]);
        $this->getJson('/api/usuarios/consultar-dni?dni=12345678')
            ->assertOk()->assertJsonPath('registrado', false);

        Mail::shouldReceive('send')->once();
        $this->postJson('/api/usuarios', $this->datos())
            ->assertCreated()->assertJsonPath('id', 1);

        $this->assertDatabaseCount('usuarios', 1);
        $this->assertDatabaseHas('usuarios', [
            'id' => 1, 'dni' => '12345678', 'email' => 'nuevo@unamba.edu.pe',
            'google_sub' => null, 'deleted_at' => null,
        ]);
        $this->assertFalse(Hash::check('clave-anterior', Usuario::findOrFail(1)->password_hash));
    }

    public function test_elimina_fisicamente_un_usuario_sin_registros_asociados(): void
    {
        DB::table('usuarios')->insert([
            'id' => 1, 'dni' => '12345678', 'nombres' => 'Nuevo', 'apellidos' => 'Usuario',
            'email' => 'nuevo@unamba.edu.pe', 'rol_id' => 1, 'activo' => 1,
        ]);

        $this->deleteJson('/api/usuarios/1')->assertOk();

        $this->assertDatabaseMissing('usuarios', ['id' => 1]);
        $this->assertDatabaseCount('usuarios', 0);
        $this->assertDatabaseHas('actores_historicos', ['id' => 1, 'nombres' => 'Nuevo']);
        $this->getJson('/api/usuarios/validar-dni?dni=12345678')
            ->assertOk()->assertJsonPath('disponible', true);
    }

    public function test_elimina_cuenta_y_conserva_autoria_de_registros_asociados(): void
    {
        DB::statement('PRAGMA foreign_keys = ON');
        DB::statement('CREATE TABLE expedientes (id INTEGER PRIMARY KEY, created_by INTEGER NOT NULL, deleted_at TEXT,
            FOREIGN KEY (created_by) REFERENCES actores_historicos(id) ON DELETE RESTRICT)');
        DB::table('usuarios')->insert([
            'id' => 1, 'dni' => '12345678', 'nombres' => 'Nuevo', 'apellidos' => 'Usuario',
            'email' => 'nuevo@unamba.edu.pe', 'rol_id' => 1, 'activo' => 1,
        ]);
        DB::table('actores_historicos')->insert([
            'id' => 1, 'nombres' => 'Nuevo', 'apellidos' => 'Usuario',
            'rol_nombre' => 'Administrador General',
        ]);
        DB::table('expedientes')->insert(['id' => 1, 'created_by' => 1]);

        $this->deleteJson('/api/usuarios/1')->assertOk();

        $this->assertDatabaseMissing('usuarios', ['id' => 1]);
        $this->assertDatabaseHas('actores_historicos', [
            'id' => 1, 'nombres' => 'Nuevo', 'apellidos' => 'Usuario', 'rol_nombre' => 'Administrador General',
        ]);
        $this->assertNotNull(DB::table('actores_historicos')->where('id', 1)->value('archivado_at'));
        $this->assertDatabaseHas('expedientes', ['id' => 1, 'created_by' => 1]);
        $this->assertSame('Nuevo Usuario', Expediente::findOrFail(1)->creador->nombre);
    }

    public function test_usuario_eliminado_no_puede_iniciar_sesion(): void
    {
        DB::table('usuarios')->insert([
            'id' => 1, 'dni' => '12345678', 'nombres' => 'Anterior', 'apellidos' => 'Usuario',
            'email' => 'anterior@unamba.edu.pe', 'rol_id' => 1,
            'password_hash' => Hash::make('clave-anterior'), 'activo' => 1,
            'deleted_at' => now(),
        ]);

        $this->withHeader('Origin', 'http://localhost:4200')->postJson('/api/login', [
            'email' => 'anterior@unamba.edu.pe', 'password' => 'clave-anterior',
        ])->assertUnprocessable();
    }

    public function test_usuario_inactivo_no_puede_usar_una_sesion_existente(): void
    {
        DB::table('usuarios')->insert([
            'id' => 1, 'dni' => '12345678', 'nombres' => 'Secretaria', 'apellidos' => 'Anterior',
            'email' => 'secretaria@unamba.edu.pe', 'rol_id' => 1,
            'password_hash' => Hash::make('clave-anterior'), 'activo' => 1,
        ]);

        $this->actingAs(Usuario::findOrFail(1));
        $this->getJson('/api/me')->assertOk();

        DB::table('usuarios')->where('id', 1)->update(['activo' => 0]);
        $this->actingAs(Usuario::findOrFail(1));

        $this->getJson('/api/me')->assertUnauthorized();
        $this->getJson('/api/usuarios')->assertUnauthorized();
    }

    public function test_no_reutiliza_el_correo_de_otro_usuario_eliminado(): void
    {
        DB::table('usuarios')->insert([
            'id' => 1, 'dni' => '87654321', 'nombres' => 'Otra', 'apellidos' => 'Persona',
            'email' => 'nuevo@unamba.edu.pe', 'rol_id' => 1, 'activo' => 0,
            'deleted_at' => now(),
        ]);

        $this->postJson('/api/usuarios', $this->datos())
            ->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('usuarios', 1);
    }

    private function datos(): array
    {
        return [
            'dni' => '12345678',
            'nombres' => 'Nuevo',
            'apellidos' => 'Usuario',
            'email' => 'nuevo@unamba.edu.pe',
            'rol_id' => 1,
            'activo' => true,
        ];
    }
}
