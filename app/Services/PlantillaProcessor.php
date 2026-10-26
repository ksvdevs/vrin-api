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
            // Word puede separar incluso los dos signos de un delimitador
            // entre distintos runs: «&lt;&lt;GENERO&gt;</w:t>...<w:t>&gt;».
            // Proteger primero cada entidad evita confundirla con los signos
            // de las etiquetas XML y permite unir solo los runs intermedios.
            $xml = str_replace(['&lt;', '&gt;'], ["\x05", "\x06"], $xml);
            $xml = preg_replace('/\x05(?:<[^>]+>)*\x05/', "\x01", $xml);
            $xml = preg_replace('/\x06(?:<[^>]+>)*\x06/', "\x02", $xml);
            $xml = str_replace(['{{', '}}'], ["\x03", "\x04"], $xml);

            // 2) Dentro de cada token, eliminar las etiquetas XML internas
            //    (runs partidos) conservando el texto del token.
            $xml = preg_replace_callback(
                ["/\x01(.*?)\x02/s", "/\x03(.*?)\x04/s"],
                fn (array $c): string => preg_replace('/<[^>]+>/', '', $c[0]),
                $xml
            );

            // 3) Restaurar delimitadores y normalizar espacios internos
            //    («<< TOKEN>>», «{{ token }}»).
            $xml = str_replace(
                ["\x01", "\x02", "\x03", "\x04", "\x05", "\x06"],
                ['<<', '>>', '{{', '}}', '&lt;', '&gt;'],
                $xml
            );

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
