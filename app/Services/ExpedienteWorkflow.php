<?php

namespace App\Services;

use App\Events\EstadoCambiado;
use App\Exceptions\DomainException;
use App\Models\CartaVrin;
use App\Models\Expediente;
use App\Models\Observacion;
use App\Models\Rendicion;
use App\Models\Resolucion;
use App\Models\RespuestaOpp;
use App\Models\Usuario;
use App\Models\ValidacionCalidad;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Máquina de estados del expediente (mapa declarativo, §1.2 del plan).
 *
 * Único punto por el que cambia `expedientes.estado`: valida rol y
 * precondiciones, ejecuta la acción de la etapa, actualiza estado/etapa y
 * dispara el evento de dominio EstadoCambiado (auditoría central, D-20).
 *
 * @phpstan-type Transicion array{roles: array<int, string>, accion: array{0: string, 1: string}|null, ejecutor: string|null, fase: int, etapa: int, requiere_completos: bool}
 */
class ExpedienteWorkflow
{
    public function __construct(private readonly DocumentGeneratorService $generator) {}

    /**
     * origen => destino => metadata. `ejecutor` null = transición declarada
     * cuya implementación llega en la fase indicada.
     *
     * @var array<string, array<string, Transicion>>
     */
    private const TRANSICIONES = [
        'OBSERVADO' => [
            'EN_REVISION_CALIDAD' => [
                'roles' => ['SECRETARIA', 'ADMINISTRADOR_GENERAL'],
                'accion' => ['subsanar', 'Completar Documentos'],
                'ejecutor' => 'subsanacion',
                'fase' => 4,
                'etapa' => 1,
                'requiere_completos' => false,
            ],
        ],
        'EN_REVISION_CALIDAD' => [
            'VALIDADO_CALIDAD' => [
                'roles' => ['CALIDAD'],
                'accion' => ['validar', 'Validar Requisitos'],
                'ejecutor' => 'validacion',
                'fase' => 4,
                'etapa' => 1,
                'requiere_completos' => true,
            ],
            'NO_CUMPLE' => [
                'roles' => ['CALIDAD'],
                'accion' => ['validar', 'Validar Requisitos'],
                'ejecutor' => 'validacion',
                'fase' => 4,
                'etapa' => 1,
                'requiere_completos' => true,
            ],
        ],
        'VALIDADO_CALIDAD' => [
            'VALIDADO_CALIDAD' => [
                'roles' => ['CALIDAD'],
                'accion' => null,
                'ejecutor' => 'validacion',
                'fase' => 4,
                'etapa' => 1,
                'requiere_completos' => true,
            ],
            'NO_CUMPLE' => [
                'roles' => ['CALIDAD'],
                'accion' => null,
                'ejecutor' => 'validacion',
                'fase' => 4,
                'etapa' => 1,
                'requiere_completos' => true,
            ],
            'EN_ESPERA_OPP' => [
                'roles' => ['SECRETARIA', 'ADMINISTRADOR_GENERAL'],
                'accion' => ['generar_carta', 'Generar Carta'],
                'ejecutor' => 'carta_vrin',
                'fase' => 6,
                'etapa' => 2,
                'requiere_completos' => false,
            ],
        ],
        'NO_CUMPLE' => [
            'VALIDADO_CALIDAD' => [
                'roles' => ['CALIDAD'],
                'accion' => null,
                'ejecutor' => 'validacion',
                'fase' => 4,
                'etapa' => 1,
                'requiere_completos' => true,
            ],
            'NO_CUMPLE' => [
                'roles' => ['CALIDAD'],
                'accion' => null,
                'ejecutor' => 'validacion',
                'fase' => 4,
                'etapa' => 1,
                'requiere_completos' => true,
            ],
        ],
        'EN_ESPERA_OPP' => [
            'DISPONIBILIDAD_CONFIRMADA' => [
                'roles' => ['SECRETARIA', 'ADMINISTRADOR_GENERAL'],
                'accion' => null,
                'ejecutor' => 'respuesta_opp',
                'fase' => 6,
                'etapa' => 2,
                'requiere_completos' => false,
            ],
            'SIN_DISPONIBILIDAD' => [
                'roles' => ['SECRETARIA', 'ADMINISTRADOR_GENERAL'],
                'accion' => null,
                'ejecutor' => 'respuesta_opp',
                'fase' => 6,
                'etapa' => 2,
                'requiere_completos' => false,
            ],
        ],
        'DISPONIBILIDAD_CONFIRMADA' => [
            'RESOLUCION_EMITIDA' => [
                'roles' => ['SECRETARIA', 'ADMINISTRADOR_GENERAL'],
                'accion' => ['generar_resolucion', 'Generar Resol.'],
                'ejecutor' => 'resolucion',
                'fase' => 7,
                'etapa' => 3,
                'requiere_completos' => false,
            ],
        ],
        'RESOLUCION_EMITIDA' => [
            'POR_RENDIR' => [
                'roles' => ['SECRETARIA', 'ADMINISTRADOR_GENERAL'],
                'accion' => null,
                'ejecutor' => 'desembolso',
                'fase' => 8,
                'etapa' => 4,
                'requiere_completos' => false,
            ],
        ],
        'POR_RENDIR' => [
            'RENDICION_VENCIDA' => [
                'roles' => ['SISTEMA'],
                'accion' => null,
                'ejecutor' => 'vencimiento',
                'fase' => 8,
                'etapa' => 4,
                'requiere_completos' => false,
            ],
            'RENDIDO' => [
                'roles' => ['SECRETARIA', 'ADMINISTRADOR_GENERAL'],
                'accion' => ['revisar_rendicion', 'Revisar Rendición'],
                'ejecutor' => 'cerrar_rendicion',
                'fase' => 8,
                'etapa' => 4,
                'requiere_completos' => false,
            ],
        ],
        'RENDICION_VENCIDA' => [
            'RENDIDO' => [
                'roles' => ['SECRETARIA', 'ADMINISTRADOR_GENERAL'],
                'accion' => ['revisar_rendicion', 'Revisar Rendición'],
                'ejecutor' => 'cerrar_rendicion',
                'fase' => 8,
                'etapa' => 4,
                'requiere_completos' => false,
            ],
        ],
    ];

    /**
     * Transiciones permitidas por (estado, rol), con su metadata de acción.
     * Incluye las declaradas cuyo ejecutor aún no existe (habilitada=false).
     * Las evaluaciones ya registradas solo admiten corrección por Calidad antes de emitir la carta.
     *
     * @return array<int, array{destino: string, accion: array{clave: string, etiqueta: string}|null, habilitada: bool}>
     */
    public function transicionesDisponibles(Expediente $expediente, Usuario $actor): array
    {
        $transiciones = self::TRANSICIONES[$expediente->estado] ?? [];

        $disponibles = [];

        foreach ($transiciones as $destino => $meta) {
            if (! in_array($actor->rol_codigo, $meta['roles'], true)) {
                continue;
            }

            if ($meta['requiere_completos'] && ! $expediente->documentos_completos) {
                continue;
            }

            if ($meta['ejecutor'] === 'validacion'
                && in_array($expediente->estado, ['VALIDADO_CALIDAD', 'NO_CUMPLE'], true)
                && ($expediente->cartaVrin()->exists()
                    || $expediente->documentosGenerados()->where('tipo', 'CARTA_VRIN')->exists())) {
                continue;
            }

            $disponibles[] = [
                'destino' => $destino,
                'accion' => $meta['accion'] !== null
                    ? ['clave' => $meta['accion'][0], 'etiqueta' => $meta['accion'][1]]
                    : null,
                'habilitada' => $meta['ejecutor'] !== null,
            ];
        }

        return $disponibles;
    }

    /**
     * Ejecuta una transición validando rol y precondiciones. Lanza
     * DomainException (409) ante cualquier incumplimiento.
     *
     * @param  array<string, mixed>  $payload
     */
    public function transicionar(Expediente $expediente, string $destino, Usuario $actor, array $payload = []): void
    {
        DB::transaction(function () use ($expediente, $destino, $actor, $payload) {
            $expediente = Expediente::query()->lockForUpdate()->findOrFail($expediente->id);
            $origen = $expediente->estado;
            $meta = self::TRANSICIONES[$origen][$destino] ?? null;

            if ($meta === null) {
                throw new DomainException("No existe la transición {$origen} → {$destino}.");
            }

            if (! in_array($actor->rol_codigo, $meta['roles'], true)) {
                throw new DomainException('Tu rol no puede ejecutar esta transición.');
            }

            if ($meta['ejecutor'] === null) {
                throw new DomainException("Transición aún no habilitada (fase {$meta['fase']}).");
            }

            if ($meta['requiere_completos'] && ! $expediente->documentos_completos) {
                throw new DomainException(
                    'El expediente debe estar EN_REVISION_CALIDAD con documentos completos para validar (RN-01/RN-12).'
                );
            }

            $eventoPayload = match ($meta['ejecutor']) {
                'validacion' => $this->ejecutarValidacion($expediente, $destino, $actor, $payload),
                'subsanacion' => $this->ejecutarSubsanacion($expediente),
                'carta_vrin' => $this->ejecutarCartaVrin($expediente, $actor, $payload),
                'respuesta_opp' => $this->ejecutarRespuestaOpp($expediente, $actor, $payload),
                'resolucion' => $this->ejecutarResolucion($expediente, $actor, $payload),
                'desembolso' => $this->ejecutarDesembolso($expediente, $actor, $payload),
                'vencimiento' => $this->ejecutarVencimiento($expediente, $actor, $payload),
                'cerrar_rendicion' => $this->ejecutarCerrarRendicion($expediente, $actor, $payload),
                default => throw new DomainException("Ejecutor desconocido: {$meta['ejecutor']}."),
            };

            $expediente->estado = $destino;
            $expediente->etapa_actual = $meta['etapa'];
            $expediente->save();

            event(new EstadoCambiado($expediente, $actor, $origen, $destino, $eventoPayload));
        });
    }

    /**
     * RN-02: la re-validación es UPDATE de la misma fila (PK expediente_id).
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function ejecutarValidacion(Expediente $expediente, string $destino, Usuario $actor, array $payload): array
    {
        $resultado = $destino === 'VALIDADO_CALIDAD' ? 'CUMPLE' : 'NO_CUMPLE';
        $checklist = $payload['checklist'] ?? [];
        $observacion = $payload['observacion'] ?? null;
        $anterior = $expediente->validacionCalidad;
        $esCorreccion = in_array($expediente->estado, ['VALIDADO_CALIDAD', 'NO_CUMPLE'], true);
        $datosAnteriores = $anterior ? [
            'resultado' => $anterior->resultado,
            'checklist' => $anterior->checklist,
            'observacion' => $anterior->observacion,
            'validado_por' => $anterior->validado_por,
            'validado_at' => $anterior->validado_at?->format('Y-m-d H:i:s'),
        ] : null;

        if ($esCorreccion) {
            if ($expediente->cartaVrin()->exists() || $expediente->documentosGenerados()->where('tipo', 'CARTA_VRIN')->exists()) {
                throw new DomainException('La evaluación ya no se puede corregir porque se generó la carta VRIN/OPP.');
            }
            if ($anterior === null) {
                throw new DomainException('El expediente no tiene una evaluación previa para corregir.');
            }
            $motivo = trim((string) ($payload['motivo_correccion'] ?? ''));
            if (mb_strlen($motivo) < 10 || mb_strlen($motivo) > 500) {
                throw new DomainException('Indica un motivo de entre 10 y 500 caracteres para corregir la evaluación.');
            }
        }

        if ($anterior !== null) {
            $expediente->observaciones()->where('etapa', 1)->where('origen', 'CALIDAD')
                ->whereNull('resuelta_at')->update(['resuelta_at' => now()]);
        }

        $expediente->cerrado_at = null;

        ValidacionCalidad::updateOrCreate(
            ['expediente_id' => $expediente->id],
            [
                'resultado' => $resultado,
                'checklist' => $checklist,
                'observacion' => $observacion,
                'validado_por' => $actor->id,
                'validado_at' => now(),
            ]
        );

        if ($resultado === 'NO_CUMPLE' && is_string($observacion) && trim($observacion) !== '') {
            $expediente->observaciones()->create([
                'etapa' => 1,
                'origen' => 'CALIDAD',
                'texto' => trim($observacion),
                'creada_por' => $actor->id,
            ]);
        }

        $validacionActual = [
            'resultado' => $resultado,
            'checklist' => $checklist,
            'observacion' => $observacion,
            'validado_por' => $actor->id,
            'validado_at' => now()->format('Y-m-d H:i:s'),
        ];

        if (! $esCorreccion) {
            return ['validacion' => $validacionActual];
        }

        return [
            'validacion' => $validacionActual + ['motivo_correccion' => $motivo],
            'validacion_anterior' => $datosAnteriores,
        ];
    }

    /**
     * Subsanación (RN-12): la secretaría marca documentos completos.
     *
     * @return array<string, mixed>
     */
    private function ejecutarSubsanacion(Expediente $expediente): array
    {
        $expediente->documentos_completos = true;

        return [];
    }

    /**
     * Fase 6 — Generación de la Carta VRIN→OPP (RN-09/RN-10/RN-13): inserta
     * la fila 1:1, congela el registro de mesa de partes del expediente y
     * genera el DOCX/PDF desde la plantilla seleccionada. La transición de
     * estado la completa transicionar() tras este ejecutor.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function actualizarCarta(Expediente $expediente, Usuario $actor, array $payload): void
    {
        DB::transaction(function () use ($expediente, $actor, $payload): void {
            $expediente = Expediente::whereKey($expediente->id)->lockForUpdate()->firstOrFail();
            if (! in_array($expediente->estado, ['EN_ESPERA_OPP', 'DISPONIBILIDAD_CONFIRMADA', 'RESOLUCION_EMITIDA'], true)) {
                throw new DomainException('La carta ya no se puede editar en esta etapa del expediente.');
            }
            $vigente = $expediente->documentosGenerados()->where('tipo', 'CARTA_VRIN')->where('es_vigente', true)->first();
            if (! $vigente || (int) $vigente->version !== (int) ($payload['version_actual'] ?? 0)) {
                throw new DomainException('La carta cambió. Actualiza el expediente antes de editarla.');
            }
            $this->ejecutarCartaVrin($expediente, $actor, $payload, true);
            $expediente->save();
            $vigente->update(['es_vigente' => false]);
        });
    }

    private function ejecutarCartaVrin(Expediente $expediente, Usuario $actor, array $payload, bool $editar = false): array
    {
        $numero = (int) $payload['numero'];
        $anio = (int) $payload['anio'];
        $numeroFormateado = str_pad((string) $numero, 3, '0', STR_PAD_LEFT).'-'.$anio;

        $duplicada = CartaVrin::where('anio', $anio)->where('numero', $numero)
            ->when($editar, fn ($query) => $query->where('expediente_id', '!=', $expediente->id))->exists();

        if ($duplicada) {
            throw new DomainException("Ya existe la carta N° {$numeroFormateado} (RN-10).");
        }

        $ciudad = $payload['ciudad'] ?? config('vrin.ciudad');
        $fecha = Carbon::parse($payload['fecha']);

        $datosCarta = [
            'expediente_id' => $expediente->id,
            'numero' => $numero,
            'anio' => $anio,
            'fecha' => $fecha->format('Y-m-d'),
            'ciudad' => $ciudad,
            'asunto' => $payload['asunto'] ?? null,
            'estado' => 'EMITIDA',
            'emitida_por' => $actor->id,
        ];
        if ($editar) {
            $expediente->cartaVrin()->firstOrFail()->update($datosCarta);
        } else {
            CartaVrin::create($datosCarta);
        }

        if (! empty($payload['registro_mp_numero'])) {
            // Persistido por el save() posterior de transicionar().
            $expediente->registro_mp_numero = $payload['registro_mp_numero'];
        }
        if (! $expediente->carta_docente_registro_numero && ! empty($payload['carta_docente_registro_numero'])) {
            $expediente->carta_docente_registro_numero = $payload['carta_docente_registro_numero'];
        }
        if (! $expediente->carta_docente_registro_fecha && ! empty($payload['carta_docente_registro_fecha'])) {
            $expediente->carta_docente_registro_fecha = $payload['carta_docente_registro_fecha'];
        }

        $fechaAceptacion = $expediente->carta_docente_registro_fecha
            ?? $payload['carta_docente_registro_fecha']
            ?? $payload['fecha_aceptacion']
            ?? $expediente->articulo->fecha_aceptacion;
        if ($fechaAceptacion) {
            $expediente->articulo->update(['fecha_aceptacion' => $fechaAceptacion]);
        }

        $this->generator->generar($expediente, 'CARTA', [
            'CIUDAD' => $ciudad,
            'FECHA_CARTA_VRIN' => $this->fechaLarga($fecha),
            'NUMERO_CARTA_VRIN' => $numeroFormateado,
            'REGISTRO_MESA_PARTES' => $expediente->carta_docente_registro_numero
                ?? $payload['carta_docente_registro_numero']
                ?? $payload['registro_mp_numero']
                ?? $expediente->registro_mp_numero
                ?? '—',
            'ASUNTO_CARTA' => $payload['asunto'] ?? null,
            'FECHA_ACEPTACION' => $fechaAceptacion ? $this->fechaLarga(Carbon::parse($fechaAceptacion)) : null,
        ], $actor);

        return [];
    }

    /**
     * Fase 6 — Registro de la respuesta OPP (RN-06): inserta la fila 1:1 con
     * la disponibilidad; si es «NO» el expediente queda cerrado.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function ejecutarRespuestaOpp(Expediente $expediente, Usuario $actor, array $payload): array
    {
        $disponible = ($payload['disponibilidad'] ?? 'NO') === 'SI';

        RespuestaOpp::create([
            'expediente_id' => $expediente->id,
            'disponibilidad' => $disponible ? 'SI' : 'NO',
            'carta_numero' => $payload['carta_numero'],
            'carta_fecha' => $payload['carta_fecha'],
            'monto_aprobado' => $disponible ? $payload['monto_aprobado'] : null,
            'meta_presupuestal' => $disponible ? $payload['meta_presupuestal'] : null,
            'especifica_gasto' => $disponible ? $payload['especifica_gasto'] : null,
            'fuente_financiamiento' => $disponible ? $payload['fuente_financiamiento'] : null,
            'registro_vrin_numero' => $payload['registro_vrin_numero'],
            'registro_vrin_fecha' => $payload['registro_vrin_fecha'],
            'registrado_por' => $actor->id,
        ]);

        if (! $disponible) {
            $expediente->cerrado_at = now();
        } else {
            $expediente->resolucion_borrador = [
                'numero' => $payload['resolucion_numero'] ?? null,
                'anio' => $payload['resolucion_anio'] ?? null,
                'fecha_emision' => $payload['resolucion_fecha_emision'] ?? null,
            ];
        }

        return [];
    }

    private function ejecutarResolucion(Expediente $expediente, Usuario $actor, array $payload): array
    {
        $numero = (int) $payload['numero'];
        $anio = (int) $payload['anio'];
        $numeroFormateado = str_pad((string) $numero, 3, '0', STR_PAD_LEFT);

        $duplicada = Resolucion::where('anio', $anio)->where('numero', $numero)->exists();

        if ($duplicada) {
            throw new DomainException("Ya existe la resolución N° {$numeroFormateado}-{$anio} (RN-10).");
        }

        $fecha = Carbon::parse($payload['fecha_emision']);

        Resolucion::create([
            'expediente_id' => $expediente->id,
            'numero' => $numero,
            'anio' => $anio,
            'fecha_emision' => $fecha->format('Y-m-d'),
            'estado' => 'EMITIDA',
            'emitida_por' => $actor->id,
        ]);

        $datos = app(DatosResolucionService::class)->construir($expediente, $numero, $anio, $fecha->format('Y-m-d'));
        $this->generator->generar($expediente, 'RESOLUCION', $datos, $actor);

        return [];
    }

    private function ejecutarDesembolso(Expediente $expediente, Usuario $actor, array $payload): array
    {
        $fechaDesembolso = Carbon::parse($payload['fecha_desembolso']);

        $plazoSvc = app(PlazoRendicionService::class);
        $fechaLimite = $plazoSvc->calcularFechaLimite($fechaDesembolso);

        Rendicion::create([
            'expediente_id' => $expediente->id,
            'fecha_desembolso' => $fechaDesembolso->format('Y-m-d'),
            'monto_desembolsado' => $payload['monto_desembolsado'],
            'fecha_limite' => $fechaLimite->format('Y-m-d'),
            'estado' => 'BORRADOR',
        ]);

        return [];
    }

    private function ejecutarVencimiento(Expediente $expediente, Usuario $actor, array $payload): array
    {
        // Enviar observación
        Observacion::create([
            'expediente_id' => $expediente->id,
            'origen' => 'SISTEMA',
            'texto' => 'La fecha límite de rendición ha vencido. Según el Art. 52, no podrá postular a nuevos apoyos económicos hasta subsanar la rendición pendiente (RN-11).',
            'etapa' => 4,
            'created_by' => $actor->id,
        ]);

        return [];
    }

    private function ejecutarCerrarRendicion(Expediente $expediente, Usuario $actor, array $payload): array
    {
        $fechaInforme = Carbon::parse($payload['fecha_informe']);
        $fechaLimite = Carbon::parse($expediente->rendicion->fecha_limite);

        $conRetraso = $fechaInforme->greaterThan($fechaLimite);

        if (array_key_exists('doi', $payload)) {
            $expediente->articulo()->firstOrFail()->update(['doi' => $payload['doi']]);
        }

        $expediente->rendicion()->update([
            'fecha_informe' => $fechaInforme->format('Y-m-d'),
            'estado' => 'CERRADA',
            'con_retraso' => $conRetraso,
            'cerrada_por' => $actor->id,
            'cerrada_at' => now(),
        ]);

        $expediente->cerrado_at = now();
        $expediente->save();

        return [];
    }

    /**
     * Formato largo de fecha de los documentos: «15 de julio del 2026».
     * Meses manual (no se asume la traducción es de Carbon instalada).
     */
    private function fechaLarga(Carbon $fecha): string
    {
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
        ];

        return "{$fecha->day} de {$meses[$fecha->month]} del {$fecha->year}";
    }
}
