<?php

namespace App\Http\Controllers;

use App\Models\Plantilla;
use App\Models\TipoDocumentoPlantilla;
use App\Services\TokenParserService;
use App\Support\TokensConocidos;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlantillaController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly TokenParserService $parser) {}

    // GET /plantillas?tipo_documento_id= — gestor de plantillas (solo admin).
    public function index(Request $request)
    {
        $this->authorize('viewAny', Plantilla::class);

        $filtros = $request->validate([
            'tipo_documento_id' => ['nullable', 'integer', Rule::exists('tipos_documento_plantilla', 'id')],
        ]);

        $plantillas = Plantilla::query()
            ->with('tipoDocumento')
            ->when($filtros['tipo_documento_id'] ?? null, fn ($q, $tipo) => $q->where('tipo_documento_id', $tipo))
            ->orderByDesc('created_at')
            ->get();

        return response()->json($plantillas->map(fn (Plantilla $p) => [
            'id' => $p->id,
            'codigo' => $p->codigo,
            'nombre' => $p->nombre,
            'modulo' => $p->modulo,
            'tipo_documento' => $p->tipoDocumento ? [
                'id' => $p->tipoDocumento->id,
                'codigo' => $p->tipoDocumento->codigo,
                'nombre' => $p->tipoDocumento->nombre,
            ] : null,
            'version' => $p->version,
            'estado' => $p->estado,
            'tokens_count' => count($p->tokens ?? []),
            'sin_mapeo' => TokensConocidos::sinMapeo($p->tokens ?? [], $p->tipoDocumento?->codigo ?? ''),
            'sha256' => $p->sha256,
            'created_at' => $p->created_at?->format('Y-m-d H:i:s'),
        ]));
    }

    // POST /plantillas — subida de una nueva versión de DOCX (inmutable).
    public function store(Request $request)
    {
        $this->authorize('create', Plantilla::class);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'tipo_documento_id' => ['required', 'integer', Rule::exists('tipos_documento_plantilla', 'id')],
            'archivo' => [
                'required',
                'file',
                'mimetypes:application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'max:25600',
            ],
        ], [
            'archivo.mimetypes' => 'El archivo debe ser un DOCX válido.',
            'archivo.max' => 'El archivo no puede superar los 25 MB.',
        ]);

        $archivo = $request->file('archivo');
        $nombreUnico = Str::lower(Str::random(40)).'.docx';
        // Convención del sistema (igual que expedientes): storage/app directo.
        $archivo->move(storage_path('app/plantillas'), $nombreUnico);
        $ruta = 'plantillas/'.$nombreUnico;
        $tokens = $this->parser->extraer(storage_path('app/'.$ruta));
        $tipo = TipoDocumentoPlantilla::findOrFail($datos['tipo_documento_id']);

        $version = (int) Plantilla::where('nombre', $datos['nombre'])
            ->where('tipo_documento_id', $tipo->id)
            ->max('version') + 1;

        $plantilla = Plantilla::create([
            'codigo' => 'PLA-TMP-'.Str::lower(Str::random(8)),
            'nombre' => $datos['nombre'],
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
        $plantilla->save();

        return response()->json([
            'id' => $plantilla->id,
            'codigo' => $plantilla->codigo,
            'nombre' => $plantilla->nombre,
            'modulo' => $plantilla->modulo,
            'tipo_documento' => ['id' => $tipo->id, 'codigo' => $tipo->codigo, 'nombre' => $tipo->nombre],
            'version' => $plantilla->version,
            'estado' => $plantilla->estado,
            'tokens' => $tokens,
            'sin_mapeo' => TokensConocidos::sinMapeo($tokens, $tipo->codigo),
            'sha256' => $plantilla->sha256,
            'created_at' => $plantilla->created_at?->format('Y-m-d H:i:s'),
        ], 201);
    }

    // PATCH /plantillas/{plantilla} — solo cambio de estado (el DOCX es inmutable).
    public function update(Request $request, Plantilla $plantilla)
    {
        $this->authorize('update', $plantilla);

        $datos = $request->validate([
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $plantilla->estado = $datos['estado'];
        $plantilla->save();

        return response()->json(['id' => $plantilla->id, 'estado' => $plantilla->estado]);
    }

    // DELETE /plantillas/{plantilla} — baja lógica (la tabla no tiene deleted_at).
    public function destroy(Plantilla $plantilla)
    {
        $this->authorize('delete', $plantilla);

        $plantilla->estado = 'INACTIVO';
        $plantilla->save();

        return response()->json(['id' => $plantilla->id, 'estado' => $plantilla->estado]);
    }
}
