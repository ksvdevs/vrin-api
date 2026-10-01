<?php

namespace App\Listeners;

use App\Events\DocumentoGenerado;
use App\Events\EstadoCambiado;
use App\Events\ExpedienteCreado;
use App\Events\PlantillaSeleccionada;
use App\Services\AuditoriaService;

/**
 * Punto central de auditoría (D-20): ningún controlador ni servicio escribe
 * en `auditoria` directamente; todo evento de dominio pasa por aquí.
 */
class EscribirAuditoria
{
    public function __construct(private AuditoriaService $auditoria) {}

    public function handleExpedienteCreado(ExpedienteCreado $evento): void
    {
        $this->auditoria->registrar('expediente.creado', [
            'expediente_id' => $evento->expediente->id,
            'usuario_id' => $evento->actor->id,
            'entidad' => 'expedientes',
            'entidad_id' => $evento->expediente->id,
            'despues' => [
                'rol' => $evento->actor->rol,
                'codigo' => $evento->expediente->codigo,
                'estado' => $evento->expediente->estado,
                'docente_id' => $evento->expediente->docente_id,
            ],
        ]);
    }

    public function handleEstadoCambiado(EstadoCambiado $evento): void
    {
        $this->auditoria->registrar($evento->accion, [
            'expediente_id' => $evento->expediente->id,
            'usuario_id' => $evento->actor->id,
            'entidad' => 'expedientes',
            'entidad_id' => $evento->expediente->id,
            'antes' => $evento->antes,
            'despues' => ['rol' => $evento->actor->rol] + $evento->despues,
        ]);
    }

    public function handlePlantillaSeleccionada(PlantillaSeleccionada $evento): void
    {
        $this->auditoria->registrar('plantilla.seleccionada', [
            'usuario_id' => $evento->actor->id,
            'entidad' => 'plantilla_seleccionada',
            'entidad_id' => $evento->tipoDocumentoId,
            'antes' => $evento->antes,
            'despues' => ['rol' => $evento->actor->rol] + $evento->despues,
        ]);
    }

    public function handleDocumentoGenerado(DocumentoGenerado $evento): void
    {
        $this->auditoria->registrar('documento.generado', [
            'expediente_id' => $evento->expediente->id,
            'usuario_id' => $evento->actor->id,
            'entidad' => 'documentos_generados',
            'entidad_id' => $evento->documento->id,
            'despues' => [
                'rol' => $evento->actor->rol,
                'tipo' => $evento->documento->tipo,
                'version' => $evento->documento->version,
                'plantilla_id' => $evento->documento->plantilla_id,
                'docx_path' => $evento->documento->docx_path,
            ],
        ]);
    }
}
