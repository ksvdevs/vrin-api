<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidacionCalidadRequest;
use App\Models\Expediente;
use App\Services\ExpedienteWorkflow;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ValidacionController extends Controller
{
    use AuthorizesRequests;

    // Fase 4 — Validación técnica de requisitos (rol CALIDAD, vía workflow).
    public function store(ValidacionCalidadRequest $request, Expediente $expediente, ExpedienteWorkflow $workflow)
    {
        $this->authorize('validarRequisitos', $expediente);

        $destino = $request->validated('resultado') === 'CUMPLE' ? 'VALIDADO_CALIDAD' : 'NO_CUMPLE';

        $workflow->transicionar($expediente, $destino, $request->user(), $request->validated());

        return response()->json([
            'estado' => $expediente->refresh()->estado,
            'validacion' => $expediente->validacionCalidad,
        ]);
    }
}
