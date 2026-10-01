<?php

namespace App\Http\Controllers;

use App\Events\PlantillaSeleccionada as EventoPlantillaSeleccionada;
use App\Models\Plantilla;
use App\Models\PlantillaSeleccionada;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PlantillaSeleccionController extends Controller
{
    use AuthorizesRequests;

    // GET /plantilla-seleccion?modulo=ARTICULOS — selección vigente por tipo.
    public function index(Request $request)
    {
        $this->authorize('seleccionar', Plantilla::class);

        $filtros = $request->validate([
            'modulo' => ['nullable', 'string', 'max:20'],
        ]);

        $modulo = $filtros['modulo'] ?? 'ARTICULOS';

        $selecciones = PlantillaSeleccionada::where('modulo', $modulo)
            ->with(['plantilla', 'tipoDocumento'])
            ->get()
            ->keyBy('tipo_documento_id');

        return response()->json(
            $selecciones->map(fn (PlantillaSeleccionada $s) => [
                'modulo' => $s->modulo,
                'tipo_documento' => $s->tipoDocumento ? [
                    'id' => $s->tipoDocumento->id,
                    'codigo' => $s->tipoDocumento->codigo,
                    'nombre' => $s->tipoDocumento->nombre,
                ] : null,
                'plantilla' => $s->plantilla ? [
                    'id' => $s->plantilla->id,
                    'codigo' => $s->plantilla->codigo,
                    'nombre' => $s->plantilla->nombre,
                    'version' => $s->plantilla->version,
                ] : null,
                'seleccionado_at' => $s->seleccionado_at?->format('Y-m-d H:i:s'),
            ])->values()
        );
    }

    // POST /plantilla-seleccion — upsert por (modulo, tipo_documento_id); RN-13.
    public function store(Request $request)
    {
        $this->authorize('seleccionar', Plantilla::class);

        $datos = $request->validate([
            'modulo' => ['nullable', 'string', 'max:20'],
            'tipo_documento_id' => ['required', 'integer', Rule::exists('tipos_documento_plantilla', 'id')],
            'plantilla_id' => ['required', 'integer', Rule::exists('plantillas', 'id')],
        ]);

        $plantilla = Plantilla::findOrFail($datos['plantilla_id']);

        if ($plantilla->tipo_documento_id !== (int) $datos['tipo_documento_id']) {
            throw ValidationException::withMessages([
                'plantilla_id' => 'La plantilla no pertenece al tipo de documento indicado.',
            ]);
        }

        $modulo = $datos['modulo'] ?? 'ARTICULOS';
        $tipoDocumentoId = (int) $datos['tipo_documento_id'];

        // OJO: la PK es compuesta y el modelo tiene $primaryKey = null, así que
        // save()/update() sobre una instancia generan un UPDATE sin WHERE (bug
        // Laravel+Eloquent). Se opera siempre por where() explícito: update()
        // del Builder es SQL directo; el insert va por create() (performInsert,
        // que no toca la PK). Nunca updateOrCreate() aquí.
        $valores = [
            'plantilla_id' => $plantilla->id,
            'seleccionado_por' => $request->user()->id,
            'seleccionado_at' => now(),
        ];

        $afectadas = PlantillaSeleccionada::where('modulo', $modulo)
            ->where('tipo_documento_id', $tipoDocumentoId)
            ->update($valores);

        if ($afectadas === 0) {
            PlantillaSeleccionada::create($valores + [
                'modulo' => $modulo,
                'tipo_documento_id' => $tipoDocumentoId,
            ]);
        }

        event(new EventoPlantillaSeleccionada(
            $modulo,
            $tipoDocumentoId,
            $plantilla->id,
            $request->user(),
        ));

        $seleccion = PlantillaSeleccionada::where('modulo', $modulo)
            ->where('tipo_documento_id', $tipoDocumentoId)
            ->firstOrFail();

        return response()->json([
            'modulo' => $seleccion->modulo,
            'tipo_documento_id' => $seleccion->tipo_documento_id,
            'plantilla_id' => $seleccion->plantilla_id,
            'seleccionado_at' => $seleccion->seleccionado_at?->format('Y-m-d H:i:s'),
        ]);
    }
}
