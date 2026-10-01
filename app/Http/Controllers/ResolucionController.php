<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerarResolucionRequest;
use App\Models\DocumentoGenerado;
use App\Models\Expediente;
use App\Models\Resolucion;
use App\Services\ExpedienteWorkflow;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ResolucionController extends Controller
{
    use AuthorizesRequests;

    public function generar(GenerarResolucionRequest $request, Expediente $expediente, ExpedienteWorkflow $workflow)
    {
        $this->authorize('generarResolucion', $expediente);

        $workflow->transicionar($expediente, 'RESOLUCION_EMITIDA', $request->user(), $request->validated());

        $documento = DocumentoGenerado::where('expediente_id', $expediente->id)
            ->where('tipo', 'RESOLUCION')
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'estado' => $expediente->refresh()->estado,
            'etapa_actual' => $expediente->etapa_actual,
            'resolucion' => $expediente->resolucion,
            'documento_generado' => $documento ? [
                'id' => $documento->id,
                'version' => $documento->version,
                'docx_path' => $documento->docx_path,
                'pdf_path' => $documento->pdf_path,
            ] : null,
        ], 201);
    }

    public function sugerencia(Request $request)
    {
        $this->authorize('sugerirResolucion', Expediente::class);

        $anio = (int) $request->validate([
            'anio' => ['required', 'integer', 'between:2020,2100'],
        ])['anio'];

        $siguiente = (int) Resolucion::where('anio', $anio)->max('numero') + 1;

        return response()->json([
            'anio' => $anio,
            'siguiente_numero' => $siguiente,
        ]);
    }
}
