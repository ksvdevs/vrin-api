<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerarCartaVrinRequest;
use App\Models\CartaVrin;
use App\Models\DocumentoGenerado;
use App\Models\Expediente;
use App\Services\DocumentGeneratorService;
use App\Services\ExpedienteWorkflow;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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

    public function preview(GenerarCartaVrinRequest $request, Expediente $expediente, DocumentGeneratorService $generator): Response
    {
        $this->authorize('generarCarta', $expediente);
        $datos = $request->validated();
        $fechaAceptacion = $expediente->carta_docente_registro_fecha
            ?? $datos['carta_docente_registro_fecha']
            ?? $datos['fecha_aceptacion']
            ?? $expediente->articulo?->fecha_aceptacion;
        $pdf = $generator->vistaPrevia($expediente, [
            'CIUDAD' => $datos['ciudad'] ?? config('vrin.ciudad'),
            'FECHA_CARTA_VRIN' => Carbon::parse($datos['fecha'])->locale('es')->translatedFormat('j \\d\\e F \\d\\e\\l Y'),
            'NUMERO_CARTA_VRIN' => str_pad((string) $datos['numero'], 3, '0', STR_PAD_LEFT).'-'.$datos['anio'],
            'REGISTRO_MESA_PARTES' => $expediente->carta_docente_registro_numero
                ?? $datos['carta_docente_registro_numero']
                ?? $datos['registro_mp_numero']
                ?? $expediente->registro_mp_numero
                ?? '—',
            'ASUNTO_CARTA' => $datos['asunto'] ?? null,
            'FECHA_ACEPTACION' => $fechaAceptacion ? Carbon::parse($fechaAceptacion)->locale('es')->translatedFormat('j \\d\\e F \\d\\e\\l Y') : null,
        ]);

        return response($pdf, 200, ['Content-Type' => 'application/pdf', 'Cache-Control' => 'no-store']);
    }

    public function update(GenerarCartaVrinRequest $request, Expediente $expediente, ExpedienteWorkflow $workflow): JsonResponse
    {
        $this->authorize('generarCarta', $expediente);
        $workflow->actualizarCarta($expediente, $request->user(), $request->validated());

        return response()->json(['mensaje' => 'Nueva versión de la carta generada.']);
    }
}
