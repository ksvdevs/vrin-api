<?php

namespace App\Events;

use App\Models\Expediente;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

class EstadoCambiado
{
    use Dispatchable;

    /**
     * @param  array<string, mixed>  $payload  Contexto del ejecutor (p. ej. validacion).
     */
    public function __construct(
        public Expediente $expediente,
        public Usuario $actor,
        public string $origen,
        public string $destino,
        public array $payload = [],
    ) {}
}
