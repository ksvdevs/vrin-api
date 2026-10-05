<?php

namespace App\Listeners;

use App\Events\DatosResolucionGuardados;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AuditarDatosResolucion
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DatosResolucionGuardados $event): void
    {
        //
    }
}
