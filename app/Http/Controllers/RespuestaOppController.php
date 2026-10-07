<?php

namespace App\Http\Controllers;

use App\Http\Requests\RespuestaOppRequest;
use App\Models\Expediente;
use App\Services\ExpedienteWorkflow;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RespuestaOppController extends Controller
{
    use AuthorizesRequests;

    // Fase 6 — Registra la respuesta OPP (RN-06) y transiciona a
    // DISPONIBILIDAD_CONFIRMADA (Sí) o SIN_DISPONIBILIDAD (No, cierra).
    public function store(RespuestaOppRequest $request, Expediente $expediente, ExpedienteWorkflow $workflow)
    {
        $this->authorize('registrarOpp', $expediente);

        $destino = $request->validated('disponibilidad') === 'SI'
            ? 'DISPONIBILIDAD_CONFIRMADA'
            : 'SIN_DISPONIBILIDAD';

        $workflow->transicionar($expediente, $destino, $request->user(), $request->validated());

        return response()->json([
            'estado' => $expediente->refresh()->estado,
            'etapa_actual' => $expediente->etapa_actual,
            'cerrado_at' => $expediente->cerrado_at?->format('Y-m-d H:i:s'),
            'respuesta_opp' => $expediente->respuestaOpp,
        ], 201);
    }

    public function update(RespuestaOppRequest $request, Expediente $expediente): JsonResponse
    {
        $this->authorize('registrarOpp', $expediente);
        abort_unless(in_array($expediente->estado, ['DISPONIBILIDAD_CONFIRMADA', 'RESOLUCION_EMITIDA'], true), 409, 'La respuesta OPP ya no se puede editar en esta etapa.');
        $datos = $request->validated();
        abort_unless($datos['disponibilidad'] === 'SI', 422, 'Para cambiar la disponibilidad, revise el expediente con Secretaría.');

        DB::transaction(function () use ($expediente, $datos, $request): void {
            $expediente->respuestaOpp()->update([
                'carta_numero' => $datos['carta_numero'],
                'carta_fecha' => $datos['carta_fecha'],
                'monto_aprobado' => $datos['monto_aprobado'],
                'meta_presupuestal' => $datos['meta_presupuestal'],
                'especifica_gasto' => $datos['especifica_gasto'],
                'fuente_financiamiento' => $datos['fuente_financiamiento'],
                'registro_vrin_numero' => $datos['registro_vrin_numero'],
                'registro_vrin_fecha' => $datos['registro_vrin_fecha'],
                'registrado_por' => $request->user()->id,
            ]);
            $expediente->update(['resolucion_borrador' => [
                'numero' => $datos['resolucion_numero'] ?? null,
                'anio' => $datos['resolucion_anio'] ?? null,
                'fecha_emision' => $datos['resolucion_fecha_emision'] ?? null,
            ]]);
        });

        return response()->json(['estado' => $expediente->estado, 'etapa_actual' => $expediente->etapa_actual,
            'respuesta_opp' => $expediente->respuestaOpp()->first(), 'resolucion_borrador' => $expediente->resolucion_borrador]);
    }
}
