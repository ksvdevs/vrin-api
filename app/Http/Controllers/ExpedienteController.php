<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrarExpedienteRequest;
use App\Http\Requests\SubirArchivoRequest;
use App\Http\Resources\ExpedienteResource;
use App\Models\Archivo;
use App\Models\DocumentoGenerado;
use App\Models\Expediente;
use App\Services\ExpedienteService;
use App\Services\ExpedienteWorkflow;
use App\Support\EstadoExpediente;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ExpedienteController extends Controller
{
    use AuthorizesRequests;

    // Fase 3 — Bandeja: paginación fija de 5 (RF-12), orden y filtros combinables.
    public function index(Request $request)
    {
        $this->authorize('viewAny', Expediente::class);

        $filtros = $request->validate([
            'estado' => ['nullable', 'string', Rule::in(EstadoExpediente::todos())],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $expedientes = Expediente::query()
            ->with(['docente.escuela.facultad', 'articulo'])
            ->orderByDesc('created_at');

        if (! empty($filtros['estado'])) {
            $expedientes->where('estado', $filtros['estado']);
        }

        if (! empty($filtros['desde'])) {
            $expedientes->whereDate('created_at', '>=', $filtros['desde']);
        }

        if (! empty($filtros['hasta'])) {
            $expedientes->whereDate('created_at', '<=', $filtros['hasta']);
        }

        return ExpedienteResource::collection($expedientes->paginate(5));
    }

    // Fase 4 — Detalle: expediente + etapas + archivos + observaciones.
    public function show(Request $request, Expediente $expediente)
    {
        $this->authorize('view', $expediente);

        $expediente->load([
            'docente.escuela.facultad',
            'escuela.facultad',
            'articulo',
            'validacionCalidad.validador',
            'cartaVrin.emisor',
            'respuestaOpp.registrador',
            'resolucion.emisor',
            'rendicion.cerrador',
            'archivos' => fn ($consulta) => $consulta->orderByDesc('created_at'),
            'observaciones' => fn ($consulta) => $consulta->orderByDesc('created_at'),
        ]);

        $docente = $expediente->docente;
        $articulo = $expediente->articulo;
        $validacion = $expediente->validacionCalidad;
        $cartaVrin = $expediente->cartaVrin;
        $respuestaOpp = $expediente->respuestaOpp;
        $resolucion = $expediente->resolucion;
        $rendicion = $expediente->rendicion;

        return response()->json([
            'id' => $expediente->id,
            'codigo' => $expediente->codigo,
            'modulo' => $expediente->modulo,
            'estado' => $expediente->estado,
            'etapa' => EstadoExpediente::etapa($expediente->estado),
            'badge' => EstadoExpediente::badge($expediente->estado),
            'etapa_actual' => $expediente->etapa_actual,
            'documentos_completos' => $expediente->documentos_completos,
            'transiciones_disponibles' => app(ExpedienteWorkflow::class)->transicionesDisponibles(
                $expediente,
                $request->user(),
            ),
            'carta_docente_numero' => $expediente->carta_docente_numero,
            'carta_docente_fecha' => $expediente->carta_docente_fecha?->format('Y-m-d'),
            'registro_mp_numero' => $expediente->registro_mp_numero,
            'cerrado_at' => $expediente->cerrado_at?->format('Y-m-d H:i:s'),
            'fecha_registro' => $expediente->created_at?->format('d/m/Y'),
            'docente' => $docente ? [
                'id' => $docente->id,
                'nombre_completo' => trim(implode(' ', array_filter([
                    $docente->grado,
                    $docente->nombres,
                    $docente->apellido_paterno,
                    $docente->apellido_materno,
                ]))),
                'dni' => $docente->dni,
                // Snapshot congelado al registrar (grado/contrato/escuela del expediente).
                'grado' => $expediente->grado ?? $docente->grado,
                'tipo_contrato' => $expediente->tipo_contrato ?? $docente->tipo_contrato,
                'email' => $docente->email,
                'escuela' => $expediente->escuela ? [
                    'id' => $expediente->escuela->id,
                    'nombre' => $expediente->escuela->nombre,
                ] : null,
                'facultad' => $expediente->escuela?->facultad ? [
                    'id' => $expediente->escuela->facultad->id,
                    'nombre' => $expediente->escuela->facultad->nombre,
                ] : null,
            ] : null,
            'articulo' => $articulo ? [
                'titulo' => $articulo->titulo,
                'revista' => $articulo->revista,
                'base_indexadora' => $articulo->base_indexadora,
                'cuartil' => $articulo->cuartil,
                'monto_solicitado' => (float) $articulo->monto_solicitado,
                'doi' => $articulo->doi,
            ] : null,
            'validacion_calidad' => $validacion ? [
                'resultado' => $validacion->resultado,
                'checklist' => $validacion->checklist,
                'observacion' => $validacion->observacion,
                'validado_at' => $validacion->validado_at?->format('Y-m-d H:i:s'),
                'validado_por' => $validacion->validador?->nombre,
            ] : null,
            'carta_vrin' => $cartaVrin ? [
                'numero' => $cartaVrin->numero,
                'anio' => $cartaVrin->anio,
                'fecha' => $cartaVrin->fecha?->format('Y-m-d'),
                'ciudad' => $cartaVrin->ciudad,
                'estado' => $cartaVrin->estado,
                'emitida_por' => $cartaVrin->emisor?->nombre,
            ] : null,
            'respuesta_opp' => $respuestaOpp ? [
                'disponibilidad' => $respuestaOpp->disponibilidad,
                'carta_numero' => $respuestaOpp->carta_numero,
                'carta_fecha' => $respuestaOpp->carta_fecha?->format('Y-m-d'),
                'monto_aprobado' => $respuestaOpp->monto_aprobado !== null ? (float) $respuestaOpp->monto_aprobado : null,
                'meta_presupuestal' => $respuestaOpp->meta_presupuestal,
                'especifica_gasto' => $respuestaOpp->especifica_gasto,
                'fuente_financiamiento' => $respuestaOpp->fuente_financiamiento,
                'registro_vrin_numero' => $respuestaOpp->registro_vrin_numero,
                'registro_vrin_fecha' => $respuestaOpp->registro_vrin_fecha?->format('Y-m-d'),
                'registrado_por' => $respuestaOpp->registrador?->nombre,
            ] : null,
            'resolucion' => $resolucion ? [
                'numero' => $resolucion->numero,
                'anio' => $resolucion->anio,
                'fecha_emision' => $resolucion->fecha_emision?->format('Y-m-d'),
                'estado' => $resolucion->estado,
                'emitida_por' => $resolucion->emisor?->nombre,
            ] : null,
            'rendicion' => $rendicion ? [
                'fecha_desembolso' => $rendicion->fecha_desembolso?->format('Y-m-d'),
                'fecha_limite' => $rendicion->fecha_limite?->format('Y-m-d'),
                'fecha_informe' => $rendicion->fecha_informe?->format('Y-m-d'),
                'estado' => $rendicion->estado,
                'con_retraso' => $rendicion->con_retraso,
                'cerrada_at' => $rendicion->cerrada_at?->format('Y-m-d H:i:s'),
                'cerrada_por' => $rendicion->cerrador?->nombre,
            ] : null,
            'archivos' => $expediente->archivos->map(fn (Archivo $archivo) => [
                'id' => $archivo->id,
                'tipo' => $archivo->tipo,
                'etapa' => $archivo->etapa,
                'nombre_original' => $archivo->nombre_original,
                'mime' => $archivo->mime,
                'tamano_bytes' => $archivo->tamano_bytes,
                'sha256' => $archivo->sha256,
                'created_at' => $archivo->created_at?->format('Y-m-d H:i:s'),
            ])->values(),
            'observaciones' => $expediente->observaciones->map(fn ($observacion) => [
                'id' => $observacion->id,
                'etapa' => $observacion->etapa,
                'origen' => $observacion->origen,
                'texto' => $observacion->texto,
                'resuelta_at' => $observacion->resuelta_at?->format('Y-m-d H:i:s'),
                'created_at' => $observacion->created_at?->format('Y-m-d H:i:s'),
            ])->values(),
            'documentos_generados' => $expediente->documentosGenerados()
                ->with('plantilla:id,codigo,version')
                ->orderByDesc('id')
                ->get()
                ->map(fn ($documento) => [
                    'id' => $documento->id,
                    'tipo' => $documento->tipo,
                    'version' => $documento->version,
                    'pdf_path' => $documento->pdf_path,
                    'es_vigente' => $documento->es_vigente,
                    'generado_at' => $documento->generado_at?->format('Y-m-d H:i:s'),
                    'plantilla' => $documento->plantilla ? [
                        'codigo' => $documento->plantilla->codigo,
                        'version' => $documento->plantilla->version,
                    ] : null,
                ])->values(),
        ]);
    }

    // Fase 6 — Descarga/preview de un documento generado (DOCX siempre
    // attachment; PDF inline salvo ?descargar=1; 404 si aún no existe el PDF).
    public function documento(Request $request, Expediente $expediente, DocumentoGenerado $documentoGenerado)
    {
        $this->authorize('view', $expediente);

        abort_unless($documentoGenerado->expediente_id === $expediente->id, 404);

        $formato = $request->validate(['formato' => ['nullable', 'in:pdf,docx']])['formato'] ?? 'pdf';

        if ($formato === 'docx') {
            return $this->respuestaArchivo(
                storage_path('app/'.$documentoGenerado->docx_path),
                basename($documentoGenerado->docx_path),
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                true
            );
        }

        if ($documentoGenerado->pdf_path === null) {
            abort(404, 'El PDF aún no está disponible (conversión en curso).');
        }

        return $this->respuestaArchivo(
            storage_path('app/'.$documentoGenerado->pdf_path),
            basename($documentoGenerado->pdf_path),
            'application/pdf',
            $request->boolean('descargar')
        );
    }

    private function respuestaArchivo(string $ruta, string $nombre, string $mime, bool $descargar)
    {
        abort_unless(is_file($ruta), 404, 'El archivo no se encuentra en el almacenamiento.');

        $respuesta = new BinaryFileResponse($ruta);
        $respuesta->headers->set('Content-Type', $mime);
        $respuesta->headers->set('Content-Disposition', $respuesta->headers->makeDisposition(
            $descargar ? ResponseHeaderBag::DISPOSITION_ATTACHMENT : ResponseHeaderBag::DISPOSITION_INLINE,
            $nombre
        ));

        return $respuesta;
    }

    // Fase 4 — Subsanación (RN-12): OBSERVADO → EN_REVISION_CALIDAD vía workflow.
    public function marcarDocumentosCompletos(Request $request, Expediente $expediente, ExpedienteWorkflow $workflow)
    {
        $this->authorize('subsanar', $expediente);

        $workflow->transicionar($expediente, 'EN_REVISION_CALIDAD', $request->user());

        return response()->json([
            'id' => $expediente->id,
            'estado' => $expediente->estado,
            'documentos_completos' => $expediente->documentos_completos,
        ]);
    }

    // Fase 3 — Descarga/preview de un archivo del expediente (RN-03: nunca se borra nada).
    public function archivo(Request $request, Expediente $expediente, Archivo $archivo)
    {
        $this->authorize('view', $expediente);

        abort_unless($archivo->expediente_id === $expediente->id, 404);

        $ruta = storage_path('app/'.$archivo->storage_path);
        abort_unless(is_file($ruta), 404, 'El archivo no se encuentra en el almacenamiento.');

        $respuesta = new BinaryFileResponse($ruta);
        $respuesta->headers->set('Content-Type', $archivo->mime ?: 'application/octet-stream');
        $respuesta->headers->set('Content-Disposition', $respuesta->headers->makeDisposition(
            $request->boolean('descargar') ? ResponseHeaderBag::DISPOSITION_ATTACHMENT : ResponseHeaderBag::DISPOSITION_INLINE,
            $archivo->nombre_original,
        ));

        return $respuesta;
    }

    public function store(RegistrarExpedienteRequest $request, ExpedienteService $servicio)
    {
        $this->authorize('create', Expediente::class);

        $resultado = $servicio->registrar($request->validated(), $request->user());

        // D-17: duplicidad blanda sin confirmación → 200 con advertencia, sin crear nada.
        if ($resultado['advertencia'] !== null) {
            return response()->json([
                'advertencia' => $resultado['advertencia'],
                'existente' => $resultado['existente'],
            ]);
        }

        return response()->json($resultado['expediente'], 201);
    }

    public function update(RegistrarExpedienteRequest $request, Expediente $expediente)
    {
        $this->authorize('update', $expediente);

        $datos = $request->validated();
        $docente = \App\Models\Docente::findOrFail($datos['docente_id']);

        \Illuminate\Support\Facades\DB::transaction(function () use ($expediente, $datos, $docente) {
            $updateData = [
                'docente_id' => $docente->id,
                'grado' => $docente->grado,
                'tipo_contrato' => $docente->tipo_contrato,
                'escuela_id' => $docente->escuela_id,
                'carta_docente_numero' => $datos['carta_docente_numero'],
                'carta_docente_fecha' => $datos['carta_docente_fecha'],
            ];

            if (in_array($expediente->estado, [EstadoExpediente::OBSERVADO, EstadoExpediente::EN_REVISION_CALIDAD])) {
                $updateData['documentos_completos'] = $datos['documentos_completos'];
                $updateData['estado'] = $datos['documentos_completos'] ? EstadoExpediente::EN_REVISION_CALIDAD : EstadoExpediente::OBSERVADO;
            }

            $expediente->update($updateData);

            $expediente->articulo()->update([
                'titulo' => $datos['titulo'],
                'revista' => $datos['revista'],
                'base_indexadora' => $datos['base_indexadora'],
                'cuartil' => $datos['cuartil'],
                'monto_solicitado' => $datos['monto_solicitado'],
                'doi' => $datos['doi'] ?? null,
            ]);
        });

        return new ExpedienteResource($expediente->fresh(['docente.escuela.facultad', 'articulo']));
    }

    public function destroy(Request $request, Expediente $expediente)
    {
        $this->authorize('delete', $expediente);
        $expediente->delete(); // Soft delete
        return response()->noContent();
    }

    // Etapa 1: subida de la carta del docente (multipart/form-data, campo `archivo`).
    public function subirArchivo(SubirArchivoRequest $request, Expediente $expediente)
    {
        $this->authorize('subirArchivo', $expediente);

        $archivoSubido = $request->file('archivo');
        $tipo = $request->validated('tipo') ?? 'CARTA_DOCENTE';
        $etapa = $request->validated('etapa') ?? SubirArchivoRequest::ETAPA_POR_TIPO[$tipo];

        // Metadatos ANTES del move(): el temporal desaparece al moverlo.
        $sha256 = hash_file('sha256', $archivoSubido->getRealPath());
        $tamano = $archivoSubido->getSize();
        $mime = $archivoSubido->getClientMimeType();
        $nombreOriginal = $archivoSubido->getClientOriginalName();

        $nombreUnico = sprintf(
            '%s_%s_%s.%s',
            strtolower($tipo),
            now()->format('YmdHis'),
            Str::random(8),
            strtolower($archivoSubido->getClientOriginalExtension())
        );

        $rutaRelativa = "expedientes/{$expediente->id}/{$nombreUnico}";
        if (! is_dir(storage_path("app/expedientes/{$expediente->id}"))) {
            mkdir(storage_path("app/expedientes/{$expediente->id}"), 0755, true);
        }
        // RN-03: los archivos jamás se borran físicamente.
        $archivoSubido->move(storage_path("app/expedientes/{$expediente->id}"), $nombreUnico);

        $archivo = $expediente->archivos()->create([
            'tipo' => $tipo,
            'etapa' => $etapa,
            'subido_por' => $request->user()->id,
            'nombre_original' => $nombreOriginal,
            'storage_path' => $rutaRelativa,
            'mime' => $mime,
            'tamano_bytes' => $tamano,
            'sha256' => $sha256,
        ]);

        return response()->json($archivo, 201);
    }
}
