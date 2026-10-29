<?php

namespace App\Http\Controllers;

use App\Models\Plantilla;
use App\Models\PlantillaSeleccionada;
use App\Models\TipoDocumentoPlantilla;
use App\Services\TokenParserService;
use App\Support\TokensConocidos;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PlantillaController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly TokenParserService $parser) {}

    public function tipos()
    {
        $this->authorize('viewAny', Plantilla::class);

        return response()->json(TipoDocumentoPlantilla::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre']));
    }

    public function versiones(Plantilla $plantilla)
    {
        $this->authorize('view', $plantilla);

        $familiaId = $plantilla->familia_id ?? $plantilla->id;
        $vigentes = PlantillaSeleccionada::pluck('plantilla_id')->all();

        return response()->json(Plantilla::withTrashed()
            ->where('familia_id', $familiaId)
            ->orderByDesc('version')->orderByDesc('id')->get()
            ->map(fn (Plantilla $version) => [
                'id' => $version->id,
                'codigo' => 'PLA-'.str_pad((string) $familiaId, 4, '0', STR_PAD_LEFT),
                'codigo_version' => $version->codigo,
                'familia_id' => $familiaId,
                'nombre' => $version->nombre,
                'modulo' => $version->modulo,
                'tipo_documento' => $version->tipoDocumento ? [
                    'id' => $version->tipoDocumento->id,
                    'codigo' => $version->tipoDocumento->codigo,
                    'nombre' => $version->tipoDocumento->nombre,
                ] : null,
                'version' => $version->version,
                'estado' => $version->estado,
                'vigente' => in_array($version->id, $vigentes, true),
                'eliminada' => $version->trashed(),
                'tokens_count' => count($version->tokens ?? []),
                'sin_mapeo' => TokensConocidos::sinMapeo($version->tokens ?? [], $version->tipoDocumento?->codigo ?? ''),
                'sha256' => $version->sha256,
                'created_at' => $version->created_at?->format('Y-m-d H:i:s'),
            ]));
    }

    // GET /plantillas?tipo_documento_id= — gestor de plantillas (solo admin).
    public function index(Request $request)
    {
        $this->authorize('viewAny', Plantilla::class);

        $filtros = $request->validate([
            'tipo_documento_id' => ['nullable', 'integer', Rule::exists('tipos_documento_plantilla', 'id')],
            'busqueda' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', Rule::in(['ACTIVO', 'INACTIVO'])],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'agrupar' => ['nullable', 'boolean'],
        ]);

        $plantillas = Plantilla::query()
            ->with('tipoDocumento')
            ->when($filtros['tipo_documento_id'] ?? null, fn ($q, $tipo) => $q->where('tipo_documento_id', $tipo))
            ->when(! empty($filtros['busqueda']), function ($q) use ($filtros) {
                $like = '%'.addcslashes(trim($filtros['busqueda']), '%_').'%';
                $q->where(fn ($w) => $w->where('codigo', 'like', $like)->orWhere('nombre', 'like', $like));
            })
            ->when($filtros['estado'] ?? null, fn ($q, $estado) => $q->where('estado', $estado))
            ->when($filtros['desde'] ?? null, fn ($q, $desde) => $q->whereDate('created_at', '>=', $desde))
            ->when($filtros['hasta'] ?? null, fn ($q, $hasta) => $q->whereDate('created_at', '<=', $hasta))
            ->when($filtros['agrupar'] ?? false, fn ($q) => $q->whereRaw('plantillas.id = (SELECT p2.id FROM plantillas p2 WHERE p2.familia_id = plantillas.familia_id AND p2.deleted_at IS NULL ORDER BY p2.version DESC, p2.id DESC LIMIT 1)'))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(min((int) ($filtros['per_page'] ?? 15), 100));

        $vigentes = PlantillaSeleccionada::pluck('plantilla_id')->all();
        $versionesVigentes = Plantilla::whereIn('id', $vigentes)
            ->get(['id', 'familia_id', 'version'])
            ->keyBy('familia_id');

        // tokens no se serializa: solo tokens_count y sin_mapeo (este último
        // requiere los nombres de los tokens, por eso se carga la columna).
        $plantillas->setCollection($plantillas->getCollection()->map(fn (Plantilla $p) => [
            'id' => $p->id,
            'codigo' => $p->codigo,
            'codigo_version' => $p->codigo,
            'familia_id' => $p->familia_id,
            'nombre' => $p->nombre,
            'modulo' => $p->modulo,
            'tipo_documento' => $p->tipoDocumento ? [
                'id' => $p->tipoDocumento->id,
                'codigo' => $p->tipoDocumento->codigo,
                'nombre' => $p->tipoDocumento->nombre,
            ] : null,
            'version' => $p->version,
            'estado' => $p->estado,
            'vigente' => in_array($p->id, $vigentes, true),
            'version_vigente' => $versionesVigentes->get($p->familia_id)?->version,
            'tokens_count' => count($p->tokens ?? []),
            'sin_mapeo' => TokensConocidos::sinMapeo($p->tokens ?? [], $p->tipoDocumento?->codigo ?? ''),
            'sha256' => $p->sha256,
            'created_at' => $p->created_at?->format('Y-m-d H:i:s'),
        ]));

        return response()->json($plantillas);
    }

    // POST /plantillas — subida de una nueva versión de DOCX (inmutable).
    public function store(Request $request)
    {
        $this->authorize('create', Plantilla::class);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'tipo_documento_id' => ['required', 'integer', Rule::exists('tipos_documento_plantilla', 'id')->where('activo', true)],
            'archivo' => [
                'required',
                'file',
                'extensions:docx',
                'max:25600',
            ],
            'plantilla_base_id' => ['nullable', 'integer', Rule::exists('plantillas', 'id')->whereNull('deleted_at')],
        ], [
            'archivo.extensions' => 'El archivo debe tener extensión .docx.',
            'archivo.max' => 'El archivo no puede superar los 25 MB.',
        ]);

        $archivo = $request->file('archivo');
        $nombreUnico = Str::lower(Str::random(40)).'.docx';
        // Convención del sistema (igual que expedientes): storage/app directo.
        $archivo->move(storage_path('app/plantillas'), $nombreUnico);
        $ruta = 'plantillas/'.$nombreUnico;
        try {
            $tokens = $this->parser->extraer(storage_path('app/'.$ruta));
        } catch (\RuntimeException $exception) {
            File::delete(storage_path('app/'.$ruta));

            throw ValidationException::withMessages([
                'archivo' => 'El archivo no es un DOCX válido.',
            ]);
        }
        if ($tokens === []) {
            File::delete(storage_path('app/'.$ruta));

            throw ValidationException::withMessages(['archivo' => 'El DOCX no contiene campos reconocibles para completar.']);
        }
        try {
            [$tipo, $plantilla] = DB::transaction(function () use ($datos, $request, $ruta, $tokens) {
                $tipo = TipoDocumentoPlantilla::whereKey($datos['tipo_documento_id'])
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->firstOrFail();
                $base = isset($datos['plantilla_base_id'])
                    ? Plantilla::whereKey($datos['plantilla_base_id'])->lockForUpdate()->firstOrFail()
                    : null;
                if ($base && $base->tipo_documento_id !== $tipo->id) {
                    throw ValidationException::withMessages(['tipo_documento_id' => 'El tipo no coincide con la plantilla original.']);
                }
                $familiaId = $base?->familia_id;
                $version = $familiaId
                    ? (int) Plantilla::withTrashed()->where('familia_id', $familiaId)->max('version') + 1
                    : 1;
                $versionAnterior = $familiaId
                    ? Plantilla::withTrashed()->where('familia_id', $familiaId)
                        ->orderByDesc('version')->orderByDesc('id')->first()
                    : null;
                $nombre = $versionAnterior?->nombre ?? $datos['nombre'];

                if ($base) {
                    $versionConCampos = Plantilla::withTrashed()->where('familia_id', $familiaId)
                        ->orderByDesc('version')->orderByDesc('id')->get()
                        ->first(fn (Plantilla $anterior) => count($anterior->tokens ?? []) > 0);
                    $faltantes = array_values(array_diff($versionConCampos?->tokens ?? [], $tokens));
                    if ($faltantes !== []) {
                        throw ValidationException::withMessages(['archivo' => 'Faltan campos de la versión anterior: '.implode(', ', $faltantes)]);
                    }
                }

                $plantilla = Plantilla::create([
                    'codigo' => 'PLA-TMP-'.Str::lower(Str::random(8)),
                    'nombre' => $nombre,
                    'familia_id' => $familiaId,
                    'modulo' => 'ARTICULOS',
                    'tipo_documento_id' => $tipo->id,
                    'version' => $version,
                    'archivo_path' => $ruta,
                    'sha256' => hash_file('sha256', storage_path('app/'.$ruta)),
                    'tokens' => $tokens,
                    'estado' => 'ACTIVO',
                    'created_by' => $request->user()->id,
                ]);

                // D-22: el código definitivo usa el id asignado (PLA-0001, …).
                $plantilla->codigo = 'PLA-'.str_pad((string) $plantilla->id, 4, '0', STR_PAD_LEFT);
                if (! $familiaId) {
                    $plantilla->familia_id = $plantilla->id;
                }
                $plantilla->save();

                return [$tipo, $plantilla];
            });
        } catch (\Throwable $exception) {
            File::delete(storage_path('app/'.$ruta));

            throw $exception;
        }

        return response()->json([
            'id' => $plantilla->id,
            'codigo' => $plantilla->codigo,
            'codigo_version' => $plantilla->codigo,
            'familia_id' => $plantilla->familia_id,
            'nombre' => $plantilla->nombre,
            'modulo' => $plantilla->modulo,
            'tipo_documento' => ['id' => $tipo->id, 'codigo' => $tipo->codigo, 'nombre' => $tipo->nombre],
            'version' => $plantilla->version,
            'estado' => $plantilla->estado,
            'tokens' => $tokens,
            'tokens_count' => count($tokens),
            'sin_mapeo' => TokensConocidos::sinMapeo($tokens, $tipo->codigo),
            'sha256' => $plantilla->sha256,
            'created_at' => $plantilla->created_at?->format('Y-m-d H:i:s'),
        ], 201);
    }

    // PATCH /plantillas/{plantilla} — edición de nombre o estado; el DOCX es inmutable.
    public function update(Request $request, Plantilla $plantilla)
    {
        $this->authorize('update', $plantilla);

        $datos = $request->validate([
            'nombre' => [
                'sometimes', 'required', 'string', 'max:150',
            ],
            'estado' => ['sometimes', 'required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        if ($datos === []) {
            throw ValidationException::withMessages(['nombre' => 'Indica un cambio para la plantilla.']);
        }

        if (($datos['estado'] ?? null) === 'INACTIVO') {
            $this->asegurarNoSeleccionada($plantilla);
        }

        $plantilla->update($datos);

        return response()->json(['id' => $plantilla->id, 'nombre' => $plantilla->nombre, 'estado' => $plantilla->estado]);
    }

    // DELETE /plantillas/{plantilla} — baja lógica; se preserva el DOCX y el historial.
    public function destroy(Plantilla $plantilla)
    {
        $this->authorize('delete', $plantilla);

        $this->asegurarNoSeleccionada($plantilla);

        $plantilla->estado = 'INACTIVO';
        $plantilla->save();
        $plantilla->delete();

        return response()->noContent();
    }

    private function asegurarNoSeleccionada(Plantilla $plantilla): void
    {
        if (PlantillaSeleccionada::where('plantilla_id', $plantilla->id)->exists()) {
            throw ValidationException::withMessages([
                'estado' => 'Selecciona otra plantilla vigente antes de desactivar esta versión.',
            ]);
        }
    }
}
