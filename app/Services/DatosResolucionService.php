<?php

namespace App\Services;

use App\Models\Expediente;
use Carbon\Carbon;

class DatosResolucionService
{
    /** @return array<string, mixed> */
    public function construir(Expediente $expediente, int $numero, int $anio, string $fechaEmision): array
    {
        $expediente->loadMissing(['respuestaOpp', 'docente', 'articulo', 'escuela']);
        $opp = $expediente->respuestaOpp;
        $docente = $expediente->docente;
        $articulo = $expediente->articulo;

        return [
            'numero_resolucion' => str_pad((string) $numero, 3, '0', STR_PAD_LEFT),
            'anio' => $anio,
            'fecha_emision' => $this->fechaLarga($fechaEmision),
            'NUMERO_REGISTRO_VRIN' => $opp->registro_vrin_numero,
            'FECHA_REGISTRO_VRIN' => $this->fechaLarga($opp->registro_vrin_fecha),
            'TITULO_ARTICULO' => $articulo->titulo,
            'GRADO' => $expediente->grado ?? $docente->grado,
            'NOMBRES' => $docente->nombres,
            'APELLIDO_PATERNO' => $docente->apellido_paterno,
            'APELLIDO_MATERNO' => $docente->apellido_materno,
            'CARTA_OPP' => $opp->carta_numero,
            'FECHA_CARTA_OPP' => $this->fechaLarga($opp->carta_fecha),
            'CARTA_DOCENTE' => 'CARTA N° '.$expediente->carta_docente_numero,
            'FECHA_CARTA_DOCENTE' => $this->fechaLarga($expediente->carta_docente_fecha),
            'REVISTA' => $articulo->revista,
            'BASE_DATOS' => $articulo->base_indexadora,
            'CUARTIL' => $articulo->cuartil,
            'MONTO_TOTAL_SOLICITADO' => number_format((float) $articulo->monto_solicitado, 2, '.', ','),
            'META' => $opp->meta_presupuestal,
            'ESPECIFICA' => $opp->especifica_gasto,
            'FUENTE' => $opp->fuente_financiamiento,
            'MONTO_APROBADO' => number_format((float) $opp->monto_aprobado, 2, '.', ','),
            'ESCUELA' => $expediente->escuela->nombre,
            'REGLAMENTO_BASE' => config('vrin.reglamento_base'),
        ];
    }

    private function fechaLarga(Carbon|string $fecha): string
    {
        return Carbon::parse($fecha)->locale('es')->translatedFormat('j \\d\\e F \\d\\e\\l Y');
    }
}
