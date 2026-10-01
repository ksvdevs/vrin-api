<?php

namespace App\Services;

use App\Events\EstadoCambiado;
use App\Exceptions\DomainException;
use App\Models\Expediente;
use App\Models\Usuario;
use App\Support\EstadoExpediente;
use Closure;
use Illuminate\Support\Facades\DB;

/**
 * Máquina de estados del expediente: única vía para cambiar `expedientes.estado` (D-05).
 *
 * El mapa declarativo (estado_origen => destino => roles/precondición/handler) es la
 * única verdad del §1.2 del plan: las transiciones de fases posteriores se agregan
 * declarando entradas nuevas, nunca con ifs sueltos fuera de aquí.
 */
class ExpedienteWorkflow
{
    /**
     * Transición OBSERVADO → EN_REVISION_CALIDAD (subsanación, RN-12).
     *
     * El actor es Secretaría/Administrador y la fila de etapa no existe aún:
     * solo se marca `documentos_completos` antes del cambio de estado.
     */
    public function marcarDocumentosCompletos(Expediente $expediente, Usuario $actor): Expediente
    {
        return $this->transicionar($expediente, 'EN_REVISION_CALIDAD', $actor, [
            'accion_auditoria' => 'expediente.subsanado',
        ]);
    }

    /**
     * Transición EN_REVISION_CALIDAD → VALIDADO_CALIDAD | NO_CUMPLE (RN-01, RN-02).
     *
     * @param  array{resultado: string, checklist: array<string, bool>, observacion?: string|null}  $payload
     */
    public function registrarValidacionCalidad(Expediente $expediente, Usuario $actor, array $payload): Expediente
    {
        return $this->transicionar(
            $expediente,
            $payload['resultado'] === 'CUMPLE' ? 'VALIDADO_CALIDAD' : 'NO_CUMPLE',
            $actor,
            ['accion_auditoria' => 'expediente.validacion_calidad'] + $payload,
        );
    }

    /**
     * Ejecuta una transición del mapa: valida rol → valida precondición de
     * estado → escribe la fila de etapa (1:1) → cambia estado/etapa →
     * dispara el evento de dominio. Todo dentro de una transacción.
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws DomainException Transición no permitida o no implementada (409).
     */
    public function transicionar(Expediente $expediente, string $destino, Usuario $actor, array $payload = []): Expediente
    {
        $transicion = $this->mapa()[$expediente->estado][$destino] ?? null;

        if ($transicion === null) {
            throw new DomainException(sprintf(
                'No se puede pasar de %s a %s: la transición no está permitida para este expediente.',
                $expediente->estado,
                $destino,
            ));
        }

        if (! in_array($actor->rol, $transicion['roles'], true)) {
            throw new DomainException(sprintf(
                'La transición a %s solo la puede ejecutar el rol %s.',
                $destino,
                implode(' o ', $transicion['roles']),
            ));
        }

        if (($transicion['precondicion'])($expediente) !== true) {
            throw new DomainException(sprintf(
                'El expediente %s no cumple la precondición para pasar a %s (requiere documentos_completos = 1).',
                $expediente->codigo,
                $destino,
            ));
        }

        if ($transicion['handler'] === null) {
            throw new DomainException(sprintf(
                'La transición a %s aún no está implementada en el sistema.',
                $destino,
            ));
        }

        return DB::transaction(function () use ($expediente, $destino, $actor, $payload, $transicion) {
            $antes = [
                'estado' => $expediente->estado,
                'etapa_actual' => $expediente->etapa_actual,
                'documentos_completos' => $expediente->documentos_completos,
            ];

            ($transicion['handler'])($expediente, $actor, $payload);

            $expediente->estado = $destino;
            // Estados sin etapa (cierres como NO_CUMPLE) conservan la etapa actual.
            $expediente->etapa_actual = EstadoExpediente::etapa($destino) ?? $expediente->etapa_actual;
            $expediente->updated_by = $actor->id;
            $expediente->save();

            EstadoCambiado::dispatch(
                $expediente,
                $actor,
                $antes,
                [
                    'estado' => $expediente->estado,
                    'etapa_actual' => $expediente->etapa_actual,
                    'documentos_completos' => $expediente->documentos_completos,
                ],
                $payload['accion_auditoria'] ?? 'estado.cambiado',
            );

            return $expediente->refresh();
        });
    }

    /**
     * Destinos que el actor puede ejecutar hoy sobre el expediente
     * (rol + precondición). Alimenta `accion_principal` del recurso.
     *
     * @return array<int, string>
     */
    public function transicionesDisponibles(Expediente $expediente, ?Usuario $actor): array
    {
        $destinos = [];

        foreach ($this->mapa()[$expediente->estado] ?? [] as $destino => $transicion) {
            if ($actor === null || ! in_array($actor->rol, $transicion['roles'], true)) {
                continue;
            }

            if (($transicion['precondicion'])($expediente) !== true) {
                continue;
            }

            $destinos[] = $destino;
        }

        return $destinos;
    }

    /**
     * Acción de UI asociada a un destino (etiqueta del botón de la bandeja).
     *
     * @return array{clave: string, etiqueta: string}|null
     */
    public function accionDeDestino(string $origen, string $destino): ?array
    {
        return $this->mapa()[$origen][$destino]['accion'] ?? null;
    }

    /**
     * Mapa declarativo de transiciones (§1.2 del plan). Las entradas con
     * `handler` null corresponden a fases posteriores (carta, OPP, resolución,
     * rendición): ya gobiernan `accion_principal` y se implementarán ahí.
     *
     * @return array<string, array<string, array{roles: array<int, string>, precondicion: Closure, handler: Closure|null, accion: array{clave: string, etiqueta: string}|null}>>
     */
    private function mapa(): array
    {
        $secretariaOAdmin = ['SECRETARIA', 'ADMINISTRADOR'];
        $siempre = fn (Expediente $e): bool => true;
        $documentosCompletos = fn (Expediente $e): bool => (bool) $e->documentos_completos;

        return [
            'OBSERVADO' => [
                'EN_REVISION_CALIDAD' => [
                    'roles' => $secretariaOAdmin,
                    'precondicion' => $siempre,
                    'handler' => fn (Expediente $e): bool => $e->forceFill(['documentos_completos' => true])->save(),
                    'accion' => ['clave' => 'completar_documentos', 'etiqueta' => 'Completar Documentos'],
                ],
            ],
            'EN_REVISION_CALIDAD' => [
                'VALIDADO_CALIDAD' => [
                    'roles' => ['CALIDAD'],
                    'precondicion' => $documentosCompletos,
                    'handler' => fn (Expediente $e, Usuario $actor, array $payload): bool => $this->escribirValidacionCalidad($e, $actor, $payload),
                    'accion' => ['clave' => 'validar', 'etiqueta' => 'Validar Requisitos'],
                ],
                'NO_CUMPLE' => [
                    'roles' => ['CALIDAD'],
                    'precondicion' => $documentosCompletos,
                    'handler' => fn (Expediente $e, Usuario $actor, array $payload): bool => $this->escribirValidacionCalidad($e, $actor, $payload),
                    'accion' => ['clave' => 'validar', 'etiqueta' => 'Validar Requisitos'],
                ],
            ],
            // Fases posteriores (§1.2): declaradas para accion_principal; sin handler aún.
            'VALIDADO_CALIDAD' => [
                'EN_ESPERA_OPP' => [
                    'roles' => $secretariaOAdmin,
                    'precondicion' => $siempre,
                    'handler' => null,
                    'accion' => ['clave' => 'generar_carta', 'etiqueta' => 'Generar Carta'],
                ],
            ],
            'EN_ESPERA_OPP' => [
                'DISPONIBILIDAD_CONFIRMADA' => [
                    'roles' => $secretariaOAdmin,
                    'precondicion' => $siempre,
                    'handler' => null,
                    'accion' => null,
                ],
                'SIN_DISPONIBILIDAD' => [
                    'roles' => $secretariaOAdmin,
                    'precondicion' => $siempre,
                    'handler' => null,
                    'accion' => null,
                ],
            ],
            'DISPONIBILIDAD_CONFIRMADA' => [
                'RESOLUCION_EMITIDA' => [
                    'roles' => $secretariaOAdmin,
                    'precondicion' => $siempre,
                    'handler' => null,
                    'accion' => ['clave' => 'generar_resolucion', 'etiqueta' => 'Generar Resol.'],
                ],
            ],
            'RESOLUCION_EMITIDA' => [
                'POR_RENDIR' => [
                    'roles' => $secretariaOAdmin,
                    'precondicion' => $siempre,
                    'handler' => null,
                    'accion' => null,
                ],
            ],
            'POR_RENDIR' => [
                'RENDICION_VENCIDA' => [
                    'roles' => ['SISTEMA'],
                    'precondicion' => fn (Expediente $e): bool => $e->rendicion?->fecha_limite?->isPast() ?? false,
                    'handler' => null,
                    'accion' => null,
                ],
                'RENDIDO' => [
                    'roles' => $secretariaOAdmin,
                    'precondicion' => fn (Expediente $e): bool => $e->archivos()->where('tipo', 'COMPROBANTE_RENDICION')->exists(),
                    'handler' => null,
                    'accion' => ['clave' => 'revisar_rendicion', 'etiqueta' => 'Revisar Rendición'],
                ],
            ],
            'RENDICION_VENCIDA' => [
                'RENDIDO' => [
                    'roles' => $secretariaOAdmin,
                    'precondicion' => fn (Expediente $e): bool => $e->archivos()->where('tipo', 'COMPROBANTE_RENDICION')->exists(),
                    'handler' => null,
                    'accion' => ['clave' => 'revisar_rendicion', 'etiqueta' => 'Revisar Rendición'],
                ],
            ],
        ];
    }

    /**
     * Etapa 1: upsert de `validaciones_calidad` (re-validar = UPDATE de la misma
     * fila, RN-02) y observación de Calidad cuando el resultado es NO_CUMPLE.
     *
     * Decisión RN-02: el mapa §1.2 no contempla re-validar tras cerrar (tras
     * VALIDADO_CALIDAD o NO_CUMPLE el expediente ya no está EN_REVISION_CALIDAD
     * y el workflow responde 409). El upsert garantiza que, si una fase futura
     * habilita la re-validación, jamás se duplique la fila.
     *
     * @param  array{resultado: string, checklist: array<string, bool>, observacion?: string|null}  $payload
     */
    private function escribirValidacionCalidad(Expediente $expediente, Usuario $actor, array $payload): bool
    {
        $expediente->validacionCalidad()->updateOrCreate(
            ['expediente_id' => $expediente->id],
            [
                'resultado' => $payload['resultado'],
                'checklist' => $payload['checklist'],
                'observacion' => $payload['observacion'] ?? null,
                'validado_por' => $actor->id,
                'validado_at' => now(),
            ],
        );

        if ($payload['resultado'] === 'NO_CUMPLE' && ! empty($payload['observacion'])) {
            $expediente->observaciones()->create([
                'etapa' => 1,
                'origen' => 'CALIDAD',
                'texto' => $payload['observacion'],
                'creada_por' => $actor->id,
            ]);
        }

        return true;
    }
}
