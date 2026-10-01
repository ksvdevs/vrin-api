<?php

namespace App\Services;

use ZipArchive;

/**
 * Extrae los tokens de un DOCX (RF-43, HU-40): abre el zip, lee
 * word/document.xml, elimina los tags XML y reconoce ambas sintaxis con
 * tolerancia a espacios internos («<< NOMBRE_TOKEN>>» y «{{ nombre_token }}»).
 */
class TokenParserService
{
    private const PATRON_CUERPO = '/<<\s*([A-Z_0-9]+)\s*>>/';

    private const PATRON_ENCABEZADO = '/\{\{\s*([a-z_0-9]+)\s*\}\}/';

    /**
     * Partes del paquete DOCX que pueden contener tokens: cuerpo,
     * encabezados y pies (los {{ }} del membrete viven en header*.xml).
     *
     * @return array<int, string>
     */
    private const PARTES = ['word/document.xml'];

    private const PATRON_PARTES_EXTRA = '/^word\/(header|footer)\d*\.xml$/';

    /**
     * @return array<int, string> lista única de nombres normalizados (trim), ordenada
     */
    public function extraer(string $rutaDocx): array
    {
        $zip = new ZipArchive;

        if ($zip->open($rutaDocx) !== true) {
            throw new \RuntimeException("No se pudo abrir el DOCX: {$rutaDocx}");
        }

        $partes = self::PARTES;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $nombre = $zip->getNameIndex($i);

            if (is_string($nombre) && preg_match(self::PATRON_PARTES_EXTRA, $nombre)) {
                $partes[] = $nombre;
            }
        }

        $texto = '';

        foreach ($partes as $parte) {
            $xml = $zip->getFromName($parte);

            if ($xml !== false) {
                $texto .= ' '.html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
            }
        }

        $zip->close();

        preg_match_all(self::PATRON_CUERPO, $texto, $cuerpo);
        preg_match_all(self::PATRON_ENCABEZADO, $texto, $encabezado);

        $tokens = array_map('trim', [...$cuerpo[1], ...$encabezado[1]]);
        $tokens = array_values(array_unique($tokens));
        sort($tokens);

        return $tokens;
    }
}
