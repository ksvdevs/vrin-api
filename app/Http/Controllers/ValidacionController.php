<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidarExpedienteRequest;
use App\Models\Expediente;
use App\Services\ExpedienteWorkflow;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ValidacionController extends Controller
{
    use AuthorizesRequests;

    // Fase 4 — Etapa 1: visto bueno de Calidad (RN-01: acción exclusiva del rol).
    public function store(ValidarExpedienteRequest $request, Expediente $expediente, ExpedienteWorkflow $workflow)
    {
        $this->authorize('validarRequisitos', $expediente);

        $expediente = $workflow->registrarValidacionCalidad(
            $expediente,
            $request->user(),
            $request->validated(),
        );

        return response()->json([
            'id' => $expediente->id,
            'codigo' => $expediente->codigo,
            'estado' => $expediente->estado,
            'validacion_calidad' => [
                'resultado' => $expediente->validacionCalidad->resultado,
                'checklist' => $expediente->validacionCalidad->checklist,
                'observacion' => $expediente->validacionCalidad->observacion,
                'validado_at' => $expediente->validacionCalidad->validado_at?->format('Y-m-d H:i:s'),
                'validado_por' => $expediente->validacionCalidad->validador?->nombre,
            ],
        ]);
    }
}
