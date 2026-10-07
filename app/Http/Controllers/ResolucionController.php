<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerarResolucionRequest;
use App\Models\DocumentoGenerado;
use App\Models\Expediente;
use App\Models\Resolucion;
use App\Services\DatosResolucionService;
use App\Services\DocumentGeneratorService;
use App\Services\ExpedienteWorkflow;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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

    public function preview(GenerarResolucionRequest $request, Expediente $expediente, DocumentGeneratorService $generator, DatosResolucionService $datosResolucion): Response
    {
        $this->authorize('generarResolucion', $expediente);
        abort_unless(in_array($expediente->estado, ['DISPONIBILIDAD_CONFIRMADA', 'RESOLUCION_EMITIDA'], true) && $expediente->respuestaOpp?->disponibilidad === 'SI', 409, 'Guarde primero la respuesta de OPP con disponibilidad confirmada.');

        $datos = $request->validated();
        $mapa = $datosResolucion->construir($expediente, (int) $datos['numero'], (int) $datos['anio'], $datos['fecha_emision']);
        $pdf = $generator->vistaPrevia($expediente, $mapa, 'RESOLUCION');

        return response($pdf, 200, ['Content-Type' => 'application/pdf', 'Cache-Control' => 'no-store']);
    }

    public function update(GenerarResolucionRequest $request, Expediente $expediente, DocumentGeneratorService $generator, DatosResolucionService $datosResolucion)
    {
        $this->authorize('generarResolucion', $expediente);
        abort_unless($expediente->estado === 'RESOLUCION_EMITIDA' && $expediente->resolucion !== null && $expediente->respuestaOpp?->disponibilidad === 'SI', 409, 'La resolución ya no se puede editar en esta etapa.');

        $datos = $request->validated();
        $duplicada = Resolucion::where('numero', $datos['numero'])->where('anio', $datos['anio'])
            ->where('expediente_id', '!=', $expediente->id)->exists();
        abort_if($duplicada, 422, 'Ya existe una resolución con ese número y año.');

        $documento = DB::transaction(function () use ($expediente, $request, $datos, $generator, $datosResolucion): DocumentoGenerado {
            $vigente = $expediente->documentosGenerados()->where('tipo', 'RESOLUCION')->where('es_vigente', true)->lockForUpdate()->first();
            abort_unless($vigente, 409, 'No se encontró la resolución vigente.');

            $mapa = $datosResolucion->construir($expediente, (int) $datos['numero'], (int) $datos['anio'], $datos['fecha_emision']);
            $vigente->update(['es_vigente' => false]);
            $nuevo = $generator->generar($expediente, 'RESOLUCION', $mapa, $request->user());
            $expediente->resolucion()->update([
                'numero' => $datos['numero'], 'anio' => $datos['anio'],
                'fecha_emision' => $datos['fecha_emision'], 'emitida_por' => $request->user()->id,
            ]);

            return $nuevo;
        });

        return response()->json([
            'estado' => $expediente->estado,
            'resolucion' => $expediente->resolucion()->first(),
            'documento_generado' => [
                'id' => $documento->id, 'version' => $documento->version,
                'docx_path' => $documento->docx_path, 'pdf_path' => $documento->pdf_path,
            ],
        ]);
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
