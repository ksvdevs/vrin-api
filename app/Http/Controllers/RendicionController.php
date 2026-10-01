<?php

namespace App\Http\Controllers;

use App\Models\Expediente;
use App\Services\ExpedienteWorkflow;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RendicionController extends Controller
{
    use AuthorizesRequests;

    public function registrarDesembolso(Request $request, Expediente $expediente, ExpedienteWorkflow $workflow)
    {
        $this->authorize('registrarDesembolso', $expediente);

        $payload = $request->validate([
            'fecha_desembolso' => ['required', 'date'],
        ]);

        $workflow->transicionar($expediente, 'POR_RENDIR', $request->user(), $payload);

        return response()->json([
            'estado' => $expediente->refresh()->estado,
            'etapa_actual' => $expediente->etapa_actual,
            'rendicion' => $expediente->rendicion,
        ], 201);
    }

    public function actualizarFechaLimite(Request $request, Expediente $expediente)
    {
        $this->authorize('actualizarFechaLimite', $expediente);

        $payload = $request->validate([
            'fecha_limite' => ['required', 'date'],
        ]);

        $rendicion = $expediente->rendicion;
        if (! $rendicion) {
            abort(404, 'Rendición no encontrada.');
        }

        $rendicion->update([
            'fecha_limite' => Carbon::parse($payload['fecha_limite'])->format('Y-m-d'),
        ]);

        // Registrar en auditoría la modificación manual.
        DB::table('auditoria')->insert([
            'expediente_id' => $expediente->id,
            'usuario_id' => $request->user()->id,
            'accion' => 'rendicion.fecha_limite_actualizada',
            'entidad' => 'rendiciones',
            'entidad_id' => $rendicion->id,
            'antes' => json_encode(['fecha_limite' => $rendicion->getOriginal('fecha_limite')]),
            'despues' => json_encode(['fecha_limite' => $payload['fecha_limite']]),
            'ip' => $request->ip(),
            'created_at' => now()->format('Y-m-d H:i:s.v'),
        ]);

        return response()->json([
            'rendicion' => $rendicion->refresh(),
        ]);
    }

    public function actualizarDoi(Request $request, Expediente $expediente)
    {
        $this->authorize('actualizarDoi', $expediente);

        $payload = $request->validate([
            'doi' => ['required', 'string', 'max:255'],
        ]);

        $articulo = $expediente->articulo;
        if (! $articulo) {
            abort(404, 'Artículo no encontrado.');
        }

        $articulo->update([
            'doi' => $payload['doi'],
        ]);

        return response()->json([
            'articulo' => $articulo->refresh(),
        ]);
    }

    public function cerrarRendicion(Request $request, Expediente $expediente, ExpedienteWorkflow $workflow)
    {
        $this->authorize('cerrarRendicion', $expediente);

        $payload = $request->validate([
            'fecha_informe' => ['required', 'date'],
        ]);

        // RN-03: Exige comprobantes.
        $tieneComprobantes = $expediente->archivos()->where('tipo', 'COMPROBANTE_RENDICION')->exists();
        if (! $tieneComprobantes) {
            throw ValidationException::withMessages([
                'comprobantes' => ['Debe adjuntar al menos un comprobante de rendición (RN-03).'],
            ]);
        }

        $workflow->transicionar($expediente, 'RENDIDO', $request->user(), $payload);

        return response()->json([
            'estado' => $expediente->refresh()->estado,
            'cerrado_at' => $expediente->cerrado_at,
            'rendicion' => $expediente->rendicion,
        ]);
    }
}
