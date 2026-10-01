<?php

namespace App\Http\Controllers;

use App\Http\Requests\RespuestaOppRequest;
use App\Models\Expediente;
use App\Services\ExpedienteWorkflow;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
}
