<?php

namespace Tests\Unit;

use App\Services\PlantillaProcessor;
use DOMDocument;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class PlantillaProcessorTest extends TestCase
{
    public function test_reemplaza_delimitadores_partidos_entre_runs_sin_corromper_el_docx(): void
    {
        $origen = tempnam(sys_get_temp_dir(), 'plantilla_');
        $destino = tempnam(sys_get_temp_dir(), 'resolucion_');

        try {
            $word = new PhpWord;
            $word->addSection()->addText('<<GENERO>> docente <<GRADO>>');
            IOFactory::createWriter($word, 'Word2007')->save($origen);

            $zip = new ZipArchive;
            $this->assertTrue($zip->open($origen) === true);
            $xml = $zip->getFromName('word/document.xml');
            $this->assertIsString($xml);
            $xml = str_replace(
                '<<GENERO>> docente <<GRADO>>',
                '&lt;&lt;GENERO&gt;</w:t></w:r><w:r><w:t>&gt; docente &lt;</w:t></w:r><w:r><w:t>&lt;GRADO&gt;&gt;',
                $xml
            );
            $this->assertStringContainsString('&lt;&lt;GENERO&gt;</w:t>', $xml);
            $zip->addFromString('word/document.xml', $xml);
            $zip->close();

            PlantillaProcessor::fijarDelimitadores('<<', '>>');
            $processor = new PlantillaProcessor($origen);
            $processor->normalizarDelimitadores();
            $processor->setValue('GENERO', 'del');
            $processor->setValue('GRADO', 'Dr.');
            $processor->saveAs($destino);

            $this->assertTrue($zip->open($destino) === true);
            $resultado = $zip->getFromName('word/document.xml');
            $zip->close();
            $this->assertIsString($resultado);
            $this->assertStringContainsString('del docente Dr.', $resultado);
            $this->assertTrue((new DOMDocument)->loadXML($resultado));
        } finally {
            PlantillaProcessor::fijarDelimitadores('${', '}');
            @unlink($origen);
            @unlink($destino);
        }
    }
}
