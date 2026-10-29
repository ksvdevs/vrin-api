<?php

namespace Tests\Unit;

use App\Services\FormatoResolucionService;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class FormatoResolucionServiceTest extends TestCase
{
    public function test_resalta_el_nombre_completo_y_el_titulo_sin_alterar_el_resto_del_texto(): void
    {
        $ruta = tempnam(sys_get_temp_dir(), 'resolucion-formato-');
        $zip = new ZipArchive;
        $zip->open($ruta, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('word/document.xml', <<<'XML'
            <w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>
              <w:p><w:r><w:t xml:space="preserve">El expediente corresponde a la docente Dra. Guadalupe </w:t></w:r><w:r><w:rPr><w:b/></w:rPr><w:t>Chaquilla Quilca</w:t></w:r><w:r><w:t>, conforme.</w:t></w:r></w:p>
              <w:p><w:r><w:rPr><w:b/></w:rPr><w:t>ARTICULO PRIMERO.- APROBAR</w:t></w:r><w:r><w:t xml:space="preserve"> el financiamiento de "Morphology and organic compounds" de la Dra. Guadalupe Chaquilla Quilca, en Scopus.</w:t></w:r></w:p>
            </w:body></w:document>
            XML);
        $zip->close();

        try {
            (new FormatoResolucionService)->aplicar($ruta, [
                'GRADO' => 'Dra.',
                'NOMBRES' => 'Guadalupe',
                'APELLIDO_PATERNO' => 'Chaquilla',
                'APELLIDO_MATERNO' => 'Quilca',
                'TITULO_ARTICULO' => 'Morphology and organic compounds',
            ]);

            $zip->open($ruta, ZipArchive::RDONLY);
            $documento = new DOMDocument;
            $documento->loadXML($zip->getFromName('word/document.xml'));
            $zip->close();
            $xpath = new DOMXPath($documento);
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            $parrafos = $xpath->query('//w:body/w:p');

            $this->assertSame(
                'El expediente corresponde a la docente Dra. Guadalupe Chaquilla Quilca, conforme.',
                $parrafos->item(0)->textContent
            );
            $this->assertSame('Dra. Guadalupe Chaquilla Quilca', $this->textoEnNegrita($xpath, $parrafos->item(0)));
            $this->assertSame('ARTICULO PRIMERO.- APROBARMorphology and organic compoundsDra. Guadalupe Chaquilla Quilca', $this->textoEnNegrita($xpath, $parrafos->item(1)));
        } finally {
            unlink($ruta);
        }
    }

    private function textoEnNegrita(DOMXPath $xpath, \DOMNode $parrafo): string
    {
        $texto = '';
        foreach ($xpath->query('./w:r[w:rPr/w:b]/w:t', $parrafo) as $nodo) {
            $texto .= $nodo->textContent;
        }

        return $texto;
    }
}
