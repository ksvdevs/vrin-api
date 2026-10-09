<?php

namespace Tests\Feature;

use App\Models\Expediente;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\ExpedienteWorkflow;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReevaluarValidacionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::connection()->getDriverName());
        DB::unprepared(<<<'SQL'
            CREATE TABLE expedientes (id INTEGER PRIMARY KEY, estado TEXT, etapa_actual INTEGER,
                documentos_completos INTEGER, cerrado_at TEXT, updated_at TEXT, deleted_at TEXT);
            CREATE TABLE validaciones_calidad (expediente_id INTEGER PRIMARY KEY, resultado TEXT,
                checklist TEXT, observacion TEXT, validado_por INTEGER, validado_at TEXT);
            CREATE TABLE observaciones (id INTEGER PRIMARY KEY AUTOINCREMENT, expediente_id INTEGER,
                etapa INTEGER, origen TEXT, texto TEXT, resuelta_at TEXT, creada_por INTEGER, created_at TEXT);
            CREATE TABLE cartas_vrin (expediente_id INTEGER PRIMARY KEY);
            CREATE TABLE documentos_generados (id INTEGER PRIMARY KEY AUTOINCREMENT, expediente_id INTEGER, tipo TEXT);
            CREATE TABLE auditoria (id INTEGER PRIMARY KEY AUTOINCREMENT, expediente_id INTEGER,
                usuario_id INTEGER, accion TEXT, entidad TEXT, entidad_id INTEGER,
                antes TEXT, despues TEXT, ip TEXT, created_at TEXT);
            SQL);
        DB::table('expedientes')->insert([
            ['id' => 1, 'estado' => 'NO_CUMPLE', 'etapa_actual' => 1, 'documentos_completos' => 1],
            ['id' => 2, 'estado' => 'VALIDADO_CALIDAD', 'etapa_actual' => 1, 'documentos_completos' => 1],
            ['id' => 3, 'estado' => 'EN_ESPERA_OPP', 'etapa_actual' => 2, 'documentos_completos' => 1],
            ['id' => 4, 'estado' => 'EN_REVISION_CALIDAD', 'etapa_actual' => 1, 'documentos_completos' => 1],
        ]);
        foreach ([1 => 'NO_CUMPLE', 2 => 'CUMPLE', 3 => 'CUMPLE'] as $id => $resultado) {
            DB::table('validaciones_calidad')->insert([
                'expediente_id' => $id,
                'resultado' => $resultado,
                'checklist' => json_encode(['carta_aceptacion' => $resultado === 'CUMPLE']),
                'observacion' => $resultado === 'NO_CUMPLE' ? 'Falta carta de aceptación' : null,
                'validado_por' => 9,
                'validado_at' => '2026-10-09 09:00:00',
            ]);
        }
        DB::table('observaciones')->insert([
            'expediente_id' => 1, 'etapa' => 1, 'origen' => 'CALIDAD',
            'texto' => 'Falta carta de aceptación', 'creada_por' => 9,
        ]);
        $this->actingAs($this->usuario(2, 'Calidad'));
    }

    public function test_calidad_corrige_no_cumple_a_cumple_y_conserva_la_decision_anterior_en_auditoria(): void
    {
        $this->postJson('/api/expedientes/1/validacion', [
            'resultado' => 'CUMPLE',
            'checklist' => [
                'carta_aceptacion' => true,
                'docente_ordinario_contratado' => true,
                'afiliacion_universidad' => true,
            ],
            'motivo_correccion' => 'Se evaluó incorrectamente la carta de aceptación.',
        ])->assertOk()->assertJsonPath('estado', 'VALIDADO_CALIDAD');

        $this->assertDatabaseHas('validaciones_calidad', ['expediente_id' => 1, 'resultado' => 'CUMPLE']);
        $this->assertNotNull(DB::table('observaciones')->where('expediente_id', 1)->value('resuelta_at'));
        $this->assertSame(1, DB::table('auditoria')->where('accion', 'expediente.validacion_calidad')->count());
        $auditoria = DB::table('auditoria')->where('accion', 'expediente.validacion_calidad')->first();
        $this->assertSame('NO_CUMPLE', json_decode($auditoria->antes, true)['resultado']);
        $this->assertSame('Se evaluó incorrectamente la carta de aceptación.', json_decode($auditoria->despues, true)['motivo_correccion']);
    }

    public function test_calidad_corrige_cumple_a_no_cumple_y_registra_la_nueva_observacion(): void
    {
        $this->postJson('/api/expedientes/2/validacion', [
            'resultado' => 'NO_CUMPLE',
            'checklist' => [
                'carta_aceptacion' => true,
                'docente_ordinario_contratado' => true,
                'afiliacion_universidad' => false,
            ],
            'observacion' => 'El artículo no declara afiliación a la universidad.',
            'motivo_correccion' => 'Se marcó afiliación conforme por error.',
        ])->assertOk()->assertJsonPath('estado', 'NO_CUMPLE');

        $this->assertDatabaseHas('validaciones_calidad', ['expediente_id' => 2, 'resultado' => 'NO_CUMPLE']);
        $this->assertSame(1, DB::table('observaciones')->where('expediente_id', 2)->whereNull('resuelta_at')->count());
    }

    public function test_exige_calidad_motivo_y_ausencia_de_carta(): void
    {
        $this->actingAs($this->usuario(1, 'Administrador General'));
        $this->postJson('/api/expedientes/1/validacion', $this->datosValidacion('Se corrigió la evaluación anterior.'))
            ->assertForbidden();

        $this->actingAs($this->usuario(2, 'Calidad'));
        $this->postJson('/api/expedientes/1/validacion', $this->datosValidacion())
            ->assertUnprocessable()->assertJsonValidationErrors('motivo_correccion');
        $this->postJson('/api/expedientes/1/validacion', $this->datosValidacion('Error'))
            ->assertUnprocessable()->assertJsonValidationErrors('motivo_correccion');
        $this->postJson('/api/expedientes/3/validacion', $this->datosValidacion('Se corrigió la evaluación anterior.'))
            ->assertStatus(409);

        DB::table('cartas_vrin')->insert(['expediente_id' => 2]);
        $this->postJson('/api/expedientes/2/validacion', $this->datosValidacion('Se corrigió la evaluación anterior.'))
            ->assertStatus(409);
        $this->assertDatabaseHas('expedientes', ['id' => 2, 'estado' => 'VALIDADO_CALIDAD']);

        DB::table('cartas_vrin')->delete();
        DB::table('documentos_generados')->insert(['expediente_id' => 2, 'tipo' => 'CARTA_VRIN']);
        $this->postJson('/api/expedientes/2/validacion', $this->datosValidacion('Se corrigió la evaluación anterior.'))
            ->assertStatus(409);
    }

    public function test_la_primera_validacion_no_requiere_motivo_de_correccion(): void
    {
        $this->postJson('/api/expedientes/4/validacion', $this->datosValidacion())
            ->assertOk()->assertJsonPath('estado', 'VALIDADO_CALIDAD');
    }

    public function test_rechaza_un_resultado_que_contradice_el_checklist(): void
    {
        $datos = $this->datosValidacion('Se corrigió la evaluación anterior.');
        $datos['checklist']['carta_aceptacion'] = false;

        $this->postJson('/api/expedientes/1/validacion', $datos)
            ->assertUnprocessable()->assertJsonValidationErrors('resultado');
        $this->assertDatabaseHas('expedientes', ['id' => 1, 'estado' => 'NO_CUMPLE']);
    }

    public function test_la_lista_ofrece_validar_solo_antes_de_la_primera_evaluacion(): void
    {
        $workflow = app(ExpedienteWorkflow::class);
        $calidad = $this->usuario(2, 'Calidad');

        $iniciales = $workflow->transicionesDisponibles(Expediente::findOrFail(4), $calidad);
        $this->assertSame('validar', $iniciales[0]['accion']['clave']);

        foreach ([1, 2] as $id) {
            $correcciones = $workflow->transicionesDisponibles(Expediente::findOrFail($id), $calidad);
            $this->assertCount(2, $correcciones);
            $this->assertNull($correcciones[0]['accion']);
            $this->assertNull($correcciones[1]['accion']);
        }

        DB::table('cartas_vrin')->insert(['expediente_id' => 2]);
        $this->assertSame([], $workflow->transicionesDisponibles(Expediente::findOrFail(2), $calidad));
    }

    private function datosValidacion(?string $motivo = null): array
    {
        $datos = [
            'resultado' => 'CUMPLE',
            'checklist' => [
                'carta_aceptacion' => true,
                'docente_ordinario_contratado' => true,
                'afiliacion_universidad' => true,
            ],
        ];
        if ($motivo !== null) {
            $datos['motivo_correccion'] = $motivo;
        }

        return $datos;
    }

    private function usuario(int $id, string $rol): Usuario
    {
        $usuario = new Usuario;
        $usuario->forceFill(['id' => $id, 'nombres' => 'Usuario', 'apellidos' => 'Prueba']);
        $usuario->setRelation('rolRef', new Rol(['nombre' => $rol]));

        return $usuario;
    }
}
