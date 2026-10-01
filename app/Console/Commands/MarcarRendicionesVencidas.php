<?php

namespace App\Console\Commands;

use App\Models\Expediente;
use App\Models\Usuario;
use App\Services\ExpedienteWorkflow;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MarcarRendicionesVencidas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vrin:marcar-rendiciones-vencidas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marca como vencidos los expedientes con fecha límite de rendición expirada';

    /**
     * Execute the console command.
     */
    public function handle(ExpedienteWorkflow $workflow)
    {
        $this->info('Iniciando verificación de rendiciones vencidas...');

        $hoy = Carbon::today()->format('Y-m-d');

        // Buscar expedientes en POR_RENDIR cuya fecha límite haya pasado
        $expedientes = Expediente::where('estado', 'POR_RENDIR')
            ->whereHas('rendicion', function ($query) use ($hoy) {
                $query->where('fecha_limite', '<', $hoy);
            })
            ->get();

        if ($expedientes->isEmpty()) {
            $this->info('No hay rendiciones vencidas hoy.');

            return 0;
        }

        // Recuperar usuario SISTEMA o simular uno para auditoría
        $sistema = Usuario::where('rol', 'SISTEMA')->first() ?? (new Usuario([
            'id' => 999999, // Un ID ficticio pero válido si no hay modelo físico guardado
            'nombres' => 'SISTEMA',
            'rol' => 'SISTEMA',
        ]));

        $marcados = 0;

        foreach ($expedientes as $expediente) {
            try {
                $workflow->transicionar($expediente, 'RENDICION_VENCIDA', $sistema, []);
                $marcados++;

                Log::info("Expediente {$expediente->codigo} marcado como RENDICION_VENCIDA.");
            } catch (\Exception $e) {
                Log::error("Error al vencer expediente {$expediente->codigo}: ".$e->getMessage());
                $this->error("Error al vencer expediente {$expediente->codigo}");
            }
        }

        $this->info("Proceso terminado. Se marcaron {$marcados} expedientes como vencidos.");

        return 0;
    }
}
