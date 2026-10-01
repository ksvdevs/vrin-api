<?php

namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;

/**
 * TemplateProcessor del SGR-VRIN: normaliza los delimitadores con espacios
 * internos («<< REGISTRO_MESA_PARTES>>», «{{ numero_resolucion }}») en las
 * partes XML temporales, para que setValue('NOMBRE', …) los encuentre tal
 * como los reporta TokenParserService (D-15).
 */
class PlantillaProcessor extends TemplateProcessor
{
    /**
     * phpword 1.1 guarda los delimitadores como estáticos protegidos y no
     * expone setters: se fijan aquí antes de cada pasada (uso secuencial).
     */
    public static function fijarDelimitadores(string $apertura, string $cierre): void
    {
        self::$macroOpeningChars = $apertura;
        self::$macroClosingChars = $cierre;
    }

    /**
     * El constructor de TemplateProcessor aplica fixBrokenMacros() a cada
     * parte (main/header/footer) con los delimitadores ESTÁTICOS vigentes:
     * en la segunda pasada ({{ }}) aún son los de la primera (<< >>) y esa
     * heurística se come los <w:t> que envuelven los tokens ya fusionados,
     * corrompiendo el XML (LibreOffice no carga el DOCX). La fusión de runs
     * la hace de forma controlada normalizarDelimitadores(), así que aquí se
     * lee la parte tal cual, sin fixBrokenMacros.
     */
    protected function readPartWithRels($fileName)
    {
        $relsFileName = $this->getRelationsName($fileName);
        $partRelations = $this->zipClass->getFromName($relsFileName);

        if ($partRelations !== false) {
            $this->tempDocumentRelations[$fileName] = $partRelations;
        }

        return $this->zipClass->getFromName($fileName);
    }

    public function normalizarDelimitadores(): void
    {
        $procesar = function (string $xml): string {
            // 0) Delimitadores escapados en el XML («&lt;&lt;», «&gt;&gt;»).
            $xml = str_replace(['&lt;&lt;', '&gt;&gt;'], ['<<', '>>'], $xml);

            // 1) Proteger los delimitadores con bytes de control: así las
            //    etiquetas XML reales que los Word trocea entre runs quedan
            //    completas y se pueden eliminar sin ambigüedad. OJO: cuando
            //    el cierre «>>» vive en su propio <w:t>, el tag-close forma
            //    «>>>» y hay que tomar las DOS últimas «>», no las dos
            //    primeras (el bracket del tag no es delimitador).
            $xml = str_replace(['<<', '{{', '}}'], ["\x01", "\x03", "\x04"], $xml);
            $xml = preg_replace('/>>(?!>)/', "\x02", $xml);

            // 2) Dentro de cada token, eliminar las etiquetas XML internas
            //    (runs partidos) conservando el texto del token.
            $xml = preg_replace_callback(
                ["/\x01(.*?)\x02/s", "/\x03(.*?)\x04/s"],
                fn (array $c): string => preg_replace('/<[^>]+>/', '', $c[0]),
                $xml
            );

            // 3) Restaurar delimitadores y normalizar espacios internos
            //    («<< TOKEN>>», «{{ token }}»).
            $xml = str_replace(["\x01", "\x02", "\x03", "\x04"], ['<<', '>>', '{{', '}}'], $xml);

            return preg_replace(
                ['/(<<)\s+/', '/(\{\{)\s+/', '/\s+(>>)/', '/\s+(\}\})/'],
                ['$1', '$1', '$1', '$1'],
                $xml
            );
        };

        $this->tempDocumentMainPart = $procesar($this->tempDocumentMainPart);

        foreach (['tempDocumentHeaders', 'tempDocumentFooters'] as $propiedad) {
            foreach ($this->{$propiedad} as $indice => $xml) {
                $this->{$propiedad}[$indice] = $procesar($xml);
            }
        }
    }
}
