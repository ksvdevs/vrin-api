<?php

namespace App\Events;

use App\Models\DocumentoGenerado as DocumentoGeneradoModel;
use App\Models\Expediente;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Evento de dominio: el motor documental emitió una nueva versión de un
 * documento del expediente (RN-09). Lo audita EscribirAuditoria.
 */
class DocumentoGenerado
{
    use Dispatchable;

    public function __construct(
        public DocumentoGeneradoModel $documento,
        public Expediente $expediente,
        public Usuario $actor,
    ) {}
}
