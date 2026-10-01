<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubirPlantillaRequest;
use App\Models\Plantilla;
use App\Models\TipoDocumentoPlantilla;
use App\Services\TokenParserService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PlantillaController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private TokenParserService $tokens) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Plantilla::class);

        $plantillas = Plantilla::query()
            ->with('tipoDocumento')
            ->when($request->query('tipo_documento_id'), function ($query, $tipoId) {
                $query->where('tipo_documento_id', $tipoId);
            })
            ->when($request->query('estado'), function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->orderBy('tipo_documento_id')
            ->orderByDesc('version')
            ->get();

        return response()->json($plantillas);
    }

    /**
     * Catálogo de tipos de documento gestionables (alimenta los selects de
     * subida y de selección vigente).
     */
    public function tipos()
    {
        $this->authorize('viewAny', Plantilla::class);

        return response()->json(
            TipoDocumentoPlantilla::where('activo', true)->orderBy('id')->get()
        );
    }

    public function store(SubirPlantillaRequest $request)
    {
        $this->authorize('create', Plantilla::class);

        $datos = $request->validated();
        $archivo = $request->file('archivo');

        return DB::transaction(function () use ($datos, $archivo, $request) {
            // Nueva subida = nueva fila: el DOCX es inmutable y la versión
            // crece por (modulo, tipo_documento_id) (Fase 5, tarea 2).
            $version = (int) Plantilla::where('modulo', $datos['modulo'] ?? 'ARTICULOS')
                ->where('tipo_documento_id', $datos['tipo_documento_id'])
                ->lockForUpdate()
                ->max('version') + 1;

            $codigo = 'PLA-'.str_pad((string) ((int) Plantilla::lockForUpdate()->max('id') + 1), 4, '0', STR_PAD_LEFT);

            $path = $archivo->storeAs('plantillas', "{$codigo}_v{$version}.docx", 'local');
            $rutaAbsoluta = Storage::disk('local')->path($path);

            $tokens = $this->tokens->extraerTokens($rutaAbsoluta);

            $plantilla = Plantilla::create([
                'codigo' => $codigo,
                'nombre' => $datos['nombre'],
                'modulo' => $datos['modulo'] ?? 'ARTICULOS',
                'tipo_documento_id' => $datos['tipo_documento_id'],
                'version' => $version,
                'archivo_path' => $path,
                'sha256' => hash_file('sha256', $rutaAbsoluta),
                'tokens' => $tokens,
                'estado' => 'ACTIVO',
                'created_by' => $request->user()->id,
            ]);

            $sinMapeo = $this->tokens->tokensSinMapeo(
                $tokens,
                $plantilla->tipoDocumento->codigo
            );

            return response()->json([
                'plantilla' => $plantilla->load('tipoDocumento'),
                'tokens' => $tokens,
                'advertencia_tokens_sin_mapeo' => $sinMapeo,
            ], 201);
        });
    }

    public function update(Request $request, Plantilla $plantilla)
    {
        $this->authorize('update', $plantilla);

        // El DOCX es inmutable: solo se permite cambiar el estado.
        $datos = $request->validate([
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
        ], [
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser ACTIVO o INACTIVO.',
        ]);

        $plantilla->update($datos);

        return response()->json($plantilla->refresh()->load('tipoDocumento'));
    }

    public function destroy(Plantilla $plantilla)
    {
        $this->authorize('delete', $plantilla);

        // Baja lógica: la tabla no tiene deleted_at; el retiro es INACTIVO y
        // el DOCX jamás se borra del disco (RNF-06).
        $plantilla->update(['estado' => 'INACTIVO']);

        return response()->json(null, 204);
    }
}
