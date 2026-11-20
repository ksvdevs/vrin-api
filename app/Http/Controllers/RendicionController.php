<?php

namespace App\Http\Controllers;

use App\Events\DesembolsoActualizado;
use App\Models\Expediente;
use App\Services\ExpedienteWorkflow;
use App\Services\PlazoRendicionService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
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
            'monto_desembolsado' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
        ]);

        $workflow->transicionar($expediente, 'POR_RENDIR', $request->user(), $payload);

        return response()->json([
            'estado' => $expediente->refresh()->estado,
            'etapa_actual' => $expediente->etapa_actual,
            'rendicion' => $expediente->rendicion,
        ], 201);
    }

    public function actualizarDesembolso(Request $request, Expediente $expediente, PlazoRendicionService $plazos): JsonResponse
    {
        $this->authorize('actualizarDesembolso', $expediente);

        $payload = $request->validate([
            'fecha_desembolso' => ['required', 'date'],
            'monto_desembolsado' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
        ]);

        $actualizado = DB::transaction(function () use ($expediente, $request, $payload, $plazos) {
            $expediente = Expediente::query()->lockForUpdate()->findOrFail($expediente->id);
            abort_unless(in_array($expediente->estado, ['POR_RENDIR', 'RENDICION_VENCIDA'], true), 409, 'La rendición ya está cerrada o aún no tiene desembolso.');

            $rendicion = $expediente->rendicion()->lockForUpdate()->firstOrFail();
            abort_unless($rendicion->estado === 'BORRADOR', 409, 'La rendición ya está cerrada.');

            $antes = [
                'fecha_desembolso' => $rendicion->fecha_desembolso->format('Y-m-d'),
                'monto_desembolsado' => $rendicion->monto_desembolsado,
                'fecha_limite' => $rendicion->fecha_limite->format('Y-m-d'),
                'estado' => $expediente->estado,
            ];

            $fechaDesembolso = Carbon::parse($payload['fecha_desembolso']);
            $fechaLimite = $plazos->calcularFechaLimite($fechaDesembolso);
            $nuevoEstado = $fechaLimite->lt(Carbon::today()) ? 'RENDICION_VENCIDA' : 'POR_RENDIR';

            $rendicion->update([
                'fecha_desembolso' => $fechaDesembolso->format('Y-m-d'),
                'monto_desembolsado' => $payload['monto_desembolsado'],
                'fecha_limite' => $fechaLimite->format('Y-m-d'),
            ]);

            if ($expediente->estado !== $nuevoEstado) {
                $expediente->update(['estado' => $nuevoEstado]);
                if ($nuevoEstado === 'POR_RENDIR') {
                    $expediente->observaciones()->where('origen', 'SISTEMA')->where('etapa', 4)
                        ->whereNull('resuelta_at')->update(['resuelta_at' => now()]);
                } else {
                    $expediente->observaciones()->create([
                        'etapa' => 4,
                        'origen' => 'SISTEMA',
                        'texto' => 'La fecha límite de rendición ha vencido tras corregir la fecha de desembolso.',
                        'creada_por' => $request->user()->id,
                    ]);
                }
            }

            event(new DesembolsoActualizado($expediente, $request->user(), $antes, [
                'fecha_desembolso' => $fechaDesembolso->format('Y-m-d'),
                'monto_desembolsado' => $rendicion->monto_desembolsado,
                'fecha_limite' => $fechaLimite->format('Y-m-d'),
                'estado' => $nuevoEstado,
            ]));

            return $rendicion;
        });

        return response()->json([
            'estado' => $actualizado->expediente->estado,
            'rendicion' => $actualizado->refresh(),
        ]);
    }

    public function actualizarFechaLimite(Request $request, Expediente $expediente)
    {
        $this->authorize('actualizarFechaLimite', $expediente);
        abort_unless(in_array($expediente->estado, ['POR_RENDIR', 'RENDICION_VENCIDA'], true), 409, 'La rendición ya no se puede editar en esta etapa.');

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
        abort_unless(in_array($expediente->estado, ['POR_RENDIR', 'RENDICION_VENCIDA'], true), 409, 'La rendición ya no se puede editar en esta etapa.');

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

        $rendicion = $expediente->rendicion;
        abort_unless($rendicion && in_array($expediente->estado, ['POR_RENDIR', 'RENDICION_VENCIDA'], true), 409, 'La rendición no está abierta.');

        $payload = $request->validate([
            'fecha_informe' => ['required', 'date', 'after_or_equal:'.$rendicion->fecha_desembolso->format('Y-m-d')],
            'doi' => ['required', 'string', 'max:255'],
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
