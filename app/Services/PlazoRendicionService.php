<?php

namespace App\Services;

use App\Models\Rendicion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class PlazoRendicionService
{
    /**
     * Calcula la fecha límite de rendición sumando N días hábiles (lunes a viernes).
     * No se consideran feriados por el momento, conforme a D-19 (solo lunes-viernes).
     */
    public function calcularFechaLimite(Carbon $fechaDesembolso): Carbon
    {
        $diasHabiles = Config::get('vrin.plazo_dias', 60);
        $fechaLimite = $fechaDesembolso->copy();

        $agregados = 0;
        while ($agregados < $diasHabiles) {
            $fechaLimite->addDay();
            if ($fechaLimite->isWeekday()) {
                $agregados++;
            }
        }

        return $fechaLimite;
    }

    /**
     * Calcula los días hábiles (lunes a viernes) entre dos fechas.
     */
    public function diasHabilesEntre(Carbon $desde, Carbon $hasta): int
    {
        if ($hasta->lessThan($desde)) {
            return -$this->diasHabilesEntre($hasta, $desde);
        }

        $dias = 0;
        $actual = $desde->copy();
        while ($actual->lessThan($hasta)) {
            $actual->addDay();
            if ($actual->isWeekday()) {
                $dias++;
            }
        }

        return $dias;
    }

    /**
     * Devuelve la cantidad de días hábiles restantes. Negativo si está vencido.
     */
    public function diasHabilesRestantes(Rendicion $rendicion): int
    {
        $hoy = Carbon::today();
        $limite = Carbon::parse($rendicion->fecha_limite);

        return $this->diasHabilesEntre($hoy, $limite);
    }
}
