<?php

namespace App\Events;

use App\Models\Expediente;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Evento de dominio: el workflow cambió el estado de un expediente (§1.2).
 */
class EstadoCambiado
{
    use Dispatchable;

    /**
     * @param  array<string, mixed>  $antes
     * @param  array<string, mixed>  $despues
     */
    public function __construct(
        public Expediente $expediente,
        public Usuario $actor,
        public array $antes,
        public array $despues,
        public string $accion = 'estado.cambiado',
    ) {}
}
