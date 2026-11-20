<?php

namespace App\Events;

use App\Models\Expediente;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

class DesembolsoActualizado
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
    ) {}
}
