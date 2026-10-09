<?php

namespace App\Services;

use App\Events\ExpedienteRegistrado;
use App\Models\Docente;
use App\Models\Expediente;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpedienteService
{
    /**
     * Etapa 1 — registro del expediente (módulo Artículos).
     *
     * Retorna ['expediente' => Expediente, 'advertencia' => null] al crear, o
     * ['expediente' => null, 'advertencia' => string, 'existente' => Expediente]
     * cuando detecta duplicidad blanda (D-17) sin confirmación del usuario.
     *
     * @throws ValidationException RN-11: docente con rendición vencida.
     */
    public function registrar(array $datos, Usuario $usuario): array
    {
        $docente = Docente::findOrFail($datos['docente_id']);

        // RN-11 / HU-24: un docente con rendición vencida no puede iniciar
        // un nuevo trámite hasta regularizar la anterior.
        $tieneRendicionVencida = Expediente::where('docente_id', $docente->id)
            ->where('estado', 'RENDICION_VENCIDA')
            ->exists();

        if ($tieneRendicionVencida) {
            throw ValidationException::withMessages([
                'docente_id' => 'El docente tiene una rendición vencida pendiente; no puede registrar un nuevo expediente hasta regularizarla (RN-11).',
            ]);
        }

        // D-17: duplicidad blanda por (docente, carta_docente_numero). No hay
        // UNIQUE en BD para permitir re-presentaciones legítimas; se advierte
        // y el usuario confirma con confirmar_duplicado=true.
        $confirmarDuplicado = filter_var($datos['confirmar_duplicado'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (! $confirmarDuplicado) {
            $existente = Expediente::where('docente_id', $docente->id)
                ->where('carta_docente_numero', $datos['carta_docente_numero'])
                ->first(['id', 'codigo', 'estado']);

            if ($existente) {
                return [
                    'expediente' => null,
                    'advertencia' => 'Ya existe un expediente de este docente con la misma carta ('.$datos['carta_docente_numero'].'). Si desea registrarlo de todos modos, reenvíe con confirmar_duplicado=true.',
                    'existente' => $existente,
                ];
            }
        }

        return DB::transaction(function () use ($datos, $docente, $usuario) {
            // Snapshot del docente (grado/contrato/escuela se congelan aquí).
            // RN-12: sin documentos completos el expediente nace OBSERVADO.
            $expediente = Expediente::create([
                'modulo' => 'ARTICULOS',
                'docente_id' => $docente->id,
                'grado' => $docente->grado,
                'tipo_contrato' => $docente->tipo_contrato,
                'escuela_id' => $docente->escuela_id,
                'carta_docente_numero' => $datos['carta_docente_numero'],
                'carta_docente_registro_numero' => $datos['carta_docente_registro_numero'],
                'carta_docente_registro_fecha' => $datos['carta_docente_registro_fecha'],
                'carta_docente_fecha' => $datos['carta_docente_fecha'],
                'documentos_completos' => $datos['documentos_completos'],
                'estado' => $datos['documentos_completos'] ? 'EN_REVISION_CALIDAD' : 'OBSERVADO',
                'etapa_actual' => 1,
                'created_by' => $usuario->id,
            ]);

            // D-22: el código se completa tras el INSERT (necesita el id).
            $expediente->codigo = 'ART-'.now()->year.'-'.str_pad((string) $expediente->id, 6, '0', STR_PAD_LEFT);
            $expediente->save();

            $expediente->articulo()->create([
                'titulo' => $datos['titulo'],
                'revista' => $datos['revista'],
                'base_indexadora' => $datos['base_indexadora'],
                'cuartil' => $datos['cuartil'],
                'monto_solicitado' => $datos['monto_solicitado'],
                'doi' => $datos['doi'] ?? null,
            ]);

            event(new ExpedienteRegistrado($expediente, $usuario));

            return [
                'expediente' => $expediente->load('articulo'),
                'advertencia' => null,
                'existente' => null,
            ];
        });
    }
}
