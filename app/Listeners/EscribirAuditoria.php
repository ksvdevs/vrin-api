<?php

namespace App\Listeners;

use App\Events\DocumentoGeneradoCreado;
use App\Events\EstadoCambiado;
use App\Events\ExpedienteRegistrado;
use App\Events\PlantillaSeleccionada;
use App\Services\AuditoriaService;

/**
 * Auditoría central (D-20): único puente entre los eventos de dominio y la
 * tabla `auditoria` (append-only). Nadie más escribe en esa tabla.
 */
class EscribirAuditoria
{
    public function __construct(private readonly AuditoriaService $auditoria) {}

    public function handle(EstadoCambiado|ExpedienteRegistrado|PlantillaSeleccionada|DocumentoGeneradoCreado $evento): void
    {
        if ($evento instanceof DocumentoGeneradoCreado) {
            $this->auditoria->registrar('documento.generado', [
                'expediente_id' => $evento->documento->expediente_id,
                'usuario_id' => $evento->actor->id,
                'entidad' => 'documentos_generados',
                'entidad_id' => $evento->documento->id,
                'despues' => [
                    'tipo' => $evento->documento->tipo,
                    'version' => $evento->documento->version,
                    'plantilla_id' => $evento->documento->plantilla_id,
                    'sha256' => $evento->documento->sha256,
                ],
            ]);

            return;
        }

        if ($evento instanceof PlantillaSeleccionada) {
            $this->auditoria->registrar('plantilla.seleccionada', [
                'usuario_id' => $evento->actor->id,
                'entidad' => 'plantilla_seleccionada',
                'entidad_id' => $evento->plantillaId,
                'despues' => [
                    'modulo' => $evento->modulo,
                    'tipo_documento_id' => $evento->tipoDocumentoId,
                    'plantilla_id' => $evento->plantillaId,
                ],
            ]);

            return;
        }

        if ($evento instanceof ExpedienteRegistrado) {
            $this->auditoria->registrar('expediente.creado', [
                'expediente_id' => $evento->expediente->id,
                'usuario_id' => $evento->usuario->id,
                'entidad' => 'expedientes',
                'entidad_id' => $evento->expediente->id,
                'despues' => [
                    'codigo' => $evento->expediente->codigo,
                    'estado' => $evento->expediente->estado,
                    'docente_id' => $evento->expediente->docente_id,
                ],
            ]);

            return;
        }

        $this->auditoria->registrar('estado.cambiado', [
            'expediente_id' => $evento->expediente->id,
            'usuario_id' => $evento->actor->id,
            'entidad' => 'expedientes',
            'entidad_id' => $evento->expediente->id,
            'antes' => ['estado' => $evento->origen],
            'despues' => ['estado' => $evento->destino],
        ]);

        if (isset($evento->payload['validacion'])) {
            $this->auditoria->registrar('expediente.validacion_calidad', [
                'expediente_id' => $evento->expediente->id,
                'usuario_id' => $evento->actor->id,
                'entidad' => 'validaciones_calidad',
                'entidad_id' => $evento->expediente->id,
                'despues' => $evento->payload['validacion'],
            ]);
        }
    }
}
