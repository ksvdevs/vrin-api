<?php

namespace App\Events;

use App\Models\Expediente;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Evento de dominio: se registró un expediente nuevo (Fase 2, RN-12).
 */
class ExpedienteCreado
{
    use Dispatchable;

    public function __construct(
        public Expediente $expediente,
        public Usuario $actor,
    ) {}
}
