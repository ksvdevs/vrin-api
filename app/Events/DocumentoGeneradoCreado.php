<?php

namespace App\Events;

use App\Models\DocumentoGenerado;
use App\Models\Usuario;
use Illuminate\Foundation\Events\Dispatchable;

class DocumentoGeneradoCreado
{
    use Dispatchable;

    public function __construct(
        public DocumentoGenerado $documento,
        public Usuario $actor,
    ) {}
}
