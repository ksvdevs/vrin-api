<?php

namespace App\Events;

use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Evento de dominio: el Administrador cambió la plantilla vigente de un
 * módulo/tipo (RN-13, HU-41). Lo audita EscribirAuditoria.
 */
class PlantillaSeleccionada
{
    use Dispatchable;

    /**
     * @param  array<string, mixed>  $antes
     * @param  array<string, mixed>  $despues
     */
    public function __construct(
        public string $modulo,
        public int $tipoDocumentoId,
        public int $plantillaId,
        public Usuario $actor,
        public array $antes,
        public array $despues,
    ) {}
}
