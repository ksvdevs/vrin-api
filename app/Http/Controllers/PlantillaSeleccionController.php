<?php

namespace App\Http\Controllers;

use App\Events\PlantillaSeleccionada as PlantillaSeleccionadaEvento;
use App\Http\Requests\SeleccionarPlantillaRequest;
use App\Models\Plantilla;
use App\Models\PlantillaSeleccionada;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlantillaSeleccionController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Plantilla::class);

        $vigentes = PlantillaSeleccionada::query()
            ->with(['plantilla.tipoDocumento', 'tipoDocumento'])
            ->when($request->query('modulo'), function ($query, $modulo) {
                $query->where('modulo', $modulo);
            })
            ->get();

        return response()->json($vigentes);
    }

    public function store(SeleccionarPlantillaRequest $request)
    {
        $this->authorize('seleccionar', Plantilla::class);

        $datos = $request->validated();

        $plantilla = Plantilla::findOrFail($datos['plantilla_id']);

        if ($plantilla->tipo_documento_id !== (int) $datos['tipo_documento_id']
            || $plantilla->modulo !== $datos['modulo']) {
            throw ValidationException::withMessages([
                'plantilla_id' => ['La plantilla no corresponde al módulo y tipo de documento indicados.'],
            ]);
        }

        if ($plantilla->estado !== 'ACTIVO') {
            throw ValidationException::withMessages([
                'plantilla_id' => ['Solo se puede seleccionar una plantilla en estado ACTIVO.'],
            ]);
        }

        $seleccion = DB::transaction(function () use ($datos, $request) {
            // PK compuesta (modulo, tipo_documento_id): exactamente una
            // plantilla vigente por combinación (RN-13).
            $anterior = PlantillaSeleccionada::where('modulo', $datos['modulo'])
                ->where('tipo_documento_id', $datos['tipo_documento_id'])
                ->lockForUpdate()
                ->first();

            // El modelo no tiene PK simple: upsert manual por la PK compuesta.
            if ($anterior) {
                PlantillaSeleccionada::where('modulo', $datos['modulo'])
                    ->where('tipo_documento_id', $datos['tipo_documento_id'])
                    ->update([
                        'plantilla_id' => $datos['plantilla_id'],
                        'seleccionado_por' => $request->user()->id,
                        'seleccionado_at' => now(),
                    ]);

                // El modelo no tiene PK simple: refresh() no aplica; se relee por la PK compuesta.
                $seleccion = PlantillaSeleccionada::where('modulo', $datos['modulo'])
                    ->where('tipo_documento_id', $datos['tipo_documento_id'])
                    ->first();
            } else {
                $seleccion = PlantillaSeleccionada::create([
                    'modulo' => $datos['modulo'],
                    'tipo_documento_id' => $datos['tipo_documento_id'],
                    'plantilla_id' => $datos['plantilla_id'],
                    'seleccionado_por' => $request->user()->id,
                    'seleccionado_at' => now(),
                ]);
            }

            PlantillaSeleccionadaEvento::dispatch(
                modulo: $datos['modulo'],
                tipoDocumentoId: (int) $datos['tipo_documento_id'],
                plantillaId: (int) $datos['plantilla_id'],
                actor: $request->user(),
                antes: ['plantilla_id' => $anterior?->plantilla_id],
                despues: [
                    'plantilla_id' => (int) $datos['plantilla_id'],
                    'modulo' => $datos['modulo'],
                    'tipo_documento_id' => (int) $datos['tipo_documento_id'],
                ],
            );

            return $seleccion;
        });

        return response()->json($seleccion->load(['plantilla.tipoDocumento', 'tipoDocumento']));
    }
}
