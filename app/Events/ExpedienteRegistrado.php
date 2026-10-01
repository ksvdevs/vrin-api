<?php

namespace App\Events;

use App\Models\Expediente;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

class ExpedienteRegistrado
{
    use Dispatchable;

    public function __construct(
        public Expediente $expediente,
        public Usuario $usuario,
    ) {}
}
