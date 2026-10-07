<?php

namespace Tests\Feature;

use App\Models\Docente;
use App\Models\Escuela;
use App\Models\Expediente;
use App\Models\ExpedienteArticulo;
use App\Models\RespuestaOpp;
use App\Services\DatosResolucionService;
use Tests\TestCase;

class DatosResolucionTest extends TestCase
{
    public function test_preview_y_generacion_comparten_los_datos_de_la_plantilla(): void
    {
        $expediente = new Expediente;
        $expediente->forceFill([
            'grado' => 'Dr.', 'carta_docente_numero' => '017-2026', 'carta_docente_fecha' => '2026-10-01',
        ]);
        $expediente->setRelation('respuestaOpp', (new RespuestaOpp)->forceFill([
            'disponibilidad' => 'SI', 'carta_numero' => '017-2026-OPP', 'carta_fecha' => '2026-10-05',
            'registro_vrin_numero' => '1538-2026-VRIN', 'registro_vrin_fecha' => '2026-10-06',
            'meta_presupuestal' => '017', 'especifica_gasto' => '2.3.27.11',
            'fuente_financiamiento' => 'Recursos ordinarios', 'monto_aprobado' => 1500,
        ]));
        $expediente->setRelation('docente', (new Docente)->forceFill([
            'nombres' => 'Ana', 'apellido_paterno' => 'Paredes', 'apellido_materno' => 'Quispe',
        ]));
        $expediente->setRelation('articulo', (new ExpedienteArticulo)->forceFill([
            'titulo' => 'Artículo científico', 'revista' => 'Revista', 'base_indexadora' => 'Scopus',
            'cuartil' => 'Q1', 'monto_solicitado' => 1500,
        ]));
        $expediente->setRelation('escuela', (new Escuela)->forceFill(['nombre' => 'Ingeniería']));

        $datos = app(DatosResolucionService::class)->construir($expediente, 13, 2026, '2026-10-07');

        $this->assertSame('013', $datos['numero_resolucion']);
        $this->assertSame('017-2026-OPP', $datos['CARTA_OPP']);
        $this->assertSame('1538-2026-VRIN', $datos['NUMERO_REGISTRO_VRIN']);
        $this->assertSame('017', $datos['META']);
        $this->assertSame('2.3.27.11', $datos['ESPECIFICA']);
        $this->assertSame('1,500.00', $datos['MONTO_APROBADO']);
        $this->assertSame('7 de octubre del 2026', $datos['fecha_emision']);
    }
}
