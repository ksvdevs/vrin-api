<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerarCartaVrinRequest;
use App\Models\CartaVrin;
use App\Models\DocumentoGenerado;
use App\Models\Expediente;
use App\Services\ExpedienteWorkflow;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CartaVrinController extends Controller
{
    use AuthorizesRequests;

    // Fase 6 — Genera la Carta VRIN→OPP: inserta cartas_vrin (RN-10) y el
    // documento desde plantilla (RN-09/RN-13), y transiciona a EN_ESPERA_OPP.
    public function store(GenerarCartaVrinRequest $request, Expediente $expediente, ExpedienteWorkflow $workflow)
    {
        $this->authorize('generarCarta', $expediente);

        $workflow->transicionar($expediente, 'EN_ESPERA_OPP', $request->user(), $request->validated());

        $documento = DocumentoGenerado::where('expediente_id', $expediente->id)
            ->where('tipo', 'CARTA_VRIN')
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'estado' => $expediente->refresh()->estado,
            'etapa_actual' => $expediente->etapa_actual,
            'carta_vrin' => $expediente->cartaVrin,
            'documento_generado' => $documento ? [
                'id' => $documento->id,
                'version' => $documento->version,
                'docx_path' => $documento->docx_path,
                'pdf_path' => $documento->pdf_path,
            ] : null,
        ], 201);
    }

    // GET /cartas-vrin/sugerencia?anio= — siguiente número libre del año (RN-10).
    public function sugerencia(Request $request)
    {
        $this->authorize('sugerirCarta', Expediente::class);

        $anio = (int) $request->validate([
            'anio' => ['required', 'integer', 'between:2020,2100'],
        ])['anio'];

        $siguiente = (int) CartaVrin::where('anio', $anio)->max('numero') + 1;

        return response()->json([
            'anio' => $anio,
            'siguiente_numero' => $siguiente,
        ]);
    }
}
