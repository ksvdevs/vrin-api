<?php

namespace App\Http\Controllers;

use App\Events\EstadoCambiado;
use App\Exceptions\DomainException;
use App\Models\DocumentoGenerado;
use App\Models\Expediente;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentoGeneradoController extends Controller
{
    use AuthorizesRequests;

    public function anular(Request $request, Expediente $expediente, DocumentoGenerado $documentoGenerado)
    {
        $this->authorize('anularDocumento', $expediente);

        abort_unless($documentoGenerado->expediente_id === $expediente->id, 404);
        abort_unless(in_array($expediente->estado, ['EN_ESPERA_OPP', 'DISPONIBILIDAD_CONFIRMADA', 'RESOLUCION_EMITIDA'], true), 409, 'El documento ya no se puede anular en esta etapa.');

        if (! $documentoGenerado->es_vigente) {
            throw new DomainException('El documento ya no está vigente.');
        }

        DB::transaction(function () use ($expediente, $documentoGenerado, $request) {
            $documentoGenerado->es_vigente = false;
            $documentoGenerado->save();

            if ($documentoGenerado->tipo === 'CARTA_VRIN') {
                $expediente->cartaVrin()->update(['estado' => 'ANULADA']);
                if ($expediente->estado === 'EN_ESPERA_OPP') {
                    $origen = $expediente->estado;
                    $expediente->update(['estado' => 'VALIDADO_CALIDAD', 'etapa_actual' => 1]);
                    event(new EstadoCambiado($expediente, $request->user(), $origen, 'VALIDADO_CALIDAD', []));
                }
            } elseif ($documentoGenerado->tipo === 'RESOLUCION') {
                $expediente->resolucion()->update(['estado' => 'ANULADA']);
                if ($expediente->estado === 'RESOLUCION_EMITIDA') {
                    $origen = $expediente->estado;
                    $expediente->update(['estado' => 'DISPONIBILIDAD_CONFIRMADA', 'etapa_actual' => 2]);
                    event(new EstadoCambiado($expediente, $request->user(), $origen, 'DISPONIBILIDAD_CONFIRMADA', []));
                }
            }

            // Registrar en auditoria (disparando un evento dummy o escribiendo directamente)
            // Ya que EstadoCambiado es para estado del expediente, usaremos un evento custom o DB directa.
            // Para simplificar, insertamos directo en auditoria.
            DB::table('auditoria')->insert([
                'expediente_id' => $expediente->id,
                'usuario_id' => $request->user()->id,
                'accion' => 'documento.anulado',
                'entidad' => 'documentos_generados',
                'entidad_id' => $documentoGenerado->id,
                'antes' => json_encode(['es_vigente' => true]),
                'despues' => json_encode(['es_vigente' => false]),
                'ip' => $request->ip(),
                'created_at' => now()->format('Y-m-d H:i:s.v'),
            ]);
        });

        return response()->json([
            'mensaje' => 'Documento anulado correctamente.',
        ]);
    }
}
