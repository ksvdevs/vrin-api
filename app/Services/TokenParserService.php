<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use ZipArchive;

/**
 * Extracción de tokens de plantillas DOCX (RF-43, D-15).
 *
 * Tolera ambas sintaxis y espacios internos:
 *  - `<<TOKEN>>`, `<< TOKEN>>`   (mayúsculas, documentos de cuerpo)
 *  - `{{ token }}`, `{{token}}`  (minúsculas, encabezados de resolución)
 *
 * En el XML del DOCX los `<`/`>` están escapados como `&lt;`/`&gt;`; por eso el
 * parser decodifica entidades antes de aplicar las expresiones regulares.
 */
class TokenParserService
{
    /**
     * Extrae los nombres canónicos de token (sin delimitadores, sin espacios)
     * de todas las partes word/*.xml del DOCX (cuerpo, encabezados, pies).
     *
     * @return list<string>
     */
    public function extraerTokens(string $rutaAbsoluta): array
    {
        $texto = $this->textoPlanoDelDocx($rutaAbsoluta);

        preg_match_all('/<<\s*([A-Z_0-9]+)\s*>>/u', $texto, $dobleAngulo);
        preg_match_all('/\{\{\s*([a-z_0-9]+)\s*\}\}/', $texto, $dobleLlave);

        $tokens = array_values(array_unique(array_merge($dobleAngulo[1], $dobleLlave[1])));
        sort($tokens);

        return $tokens;
    }

    /**
     * Tokens del DOCX que no tienen mapeo conocido para el tipo de documento
     * (catálogo `vrin.tokens_conocidos`). Se reportan como advertencia (RF-43).
     *
     * @param  list<string>  $tokens
     * @return list<string>
     */
    public function tokensSinMapeo(array $tokens, string $tipoDocumentoCodigo): array
    {
        /** @var array<string, list<string>> $catalogo */
        $catalogo = config('vrin.tokens_conocidos', []);
        $conocidos = $catalogo[$tipoDocumentoCodigo] ?? [];

        return array_values(array_diff($tokens, $conocidos));
    }

    /**
     * Genera una copia normalizada del DOCX donde cada token, en cualquiera de
     * las dos sintaxis, queda como `${NOMBRE}` (delimitadores nativos de
     * TemplateProcessor). La plantilla almacenada nunca se modifica (D-13).
     */
    public function normalizarCopia(string $rutaOrigen, string $rutaDestino): void
    {
        $origen = new ZipArchive;

        if ($origen->open($rutaOrigen, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException("No se pudo abrir la plantilla DOCX: {$rutaOrigen}");
        }

        $destino = new ZipArchive;

        if ($destino->open($rutaDestino, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $origen->close();
            throw new RuntimeException("No se pudo crear la copia temporal: {$rutaDestino}");
        }

        for ($i = 0; $i < $origen->numFiles; $i++) {
            $nombre = $origen->getNameIndex($i);
            $contenido = $origen->getFromIndex($i);

            if ($contenido !== false && preg_match('#^word/(document|header\d*|footer\d*)\.xml$#', $nombre)) {
                $contenido = $this->normalizarTokensXml($contenido);
            }

            $destino->addFromString($nombre, $contenido === false ? '' : $contenido);
        }

        $origen->close();
        $destino->close();
    }

    /**
     * Reescribe una parte word/*.xml convirtiendo cada token a `${NOMBRE}`.
     *
     * Los tokens pueden estar partidos entre varios runs (`<w:t>`) por culpa
     * del corrector de Word (caso real: `<< REGISTRO_MESA_PARTES>>` en tres
     * runs). Se trabaja por párrafo: se concatena el texto de sus nodos w:t,
     * se localizan los tokens y se redistribuye el `${NOMBRE}` sobre los nodos
     * originales, sin tocar el formato del resto del documento.
     */
    private function normalizarTokensXml(string $xml): string
    {
        $dom = new DOMDocument;
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;

        if (! $dom->loadXML($xml)) {
            return $xml;
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        /** @var \DOMElement $parrafo */
        foreach ($xpath->query('//w:p') ?: [] as $parrafo) {
            $this->normalizarParrafo($parrafo, $xpath);
        }

        return $dom->saveXML() ?: $xml;
    }

    /**
     * Reemplaza dentro de un párrafo los tokens (posiblemente partidos entre
     * nodos w:t) por `${NOMBRE}` en el nodo donde empieza el token.
     */
    private function normalizarParrafo(DOMElement $parrafo, DOMXPath $xpath): void
    {
        /** @var list<\DOMElement> $nodos */
        $nodos = [];

        foreach ($xpath->query('.//w:t', $parrafo) ?: [] as $nodo) {
            /** @var \DOMElement $nodo */
            $nodos[] = $nodo;
        }

        if ($nodos === []) {
            return;
        }

        $textos = array_map(fn (DOMElement $nodo) => $nodo->textContent, $nodos);
        $completo = implode('', $textos);

        $encontrados = preg_match_all(
            '/<<\s*[A-Z_0-9]+\s*>>|\{\{\s*[a-z_0-9]+\s*\}\}/u',
            $completo,
            $coincidencias,
            PREG_OFFSET_CAPTURE
        );

        if ($encontrados === false || $encontrados === 0) {
            return;
        }

        /** @var list<array{int, int, string}> $reemplazos [inicio, fin, nombre] */
        $reemplazos = [];

        foreach ($coincidencias[0] as [$token, $offset]) {
            preg_match('/[A-Za-z_0-9]+/', $token, $nombre);
            $reemplazos[] = [$offset, $offset + strlen($token), $nombre[0]];
        }

        $posicion = 0;

        foreach ($nodos as $indice => $nodo) {
            $inicio = $posicion;
            $fin = $posicion + strlen($textos[$indice]);
            $posicion = $fin;

            $nuevo = '';
            $cursor = $inicio;

            foreach ($reemplazos as [$tokenInicio, $tokenFin, $nombre]) {
                if ($tokenFin <= $inicio || $tokenInicio >= $fin) {
                    continue;
                }

                $nuevo .= substr($completo, $cursor, max(0, min($tokenInicio, $fin) - $cursor));

                if ($tokenInicio >= $inicio && $tokenInicio < $fin) {
                    $nuevo .= '${'.$nombre.'}';
                }

                $cursor = max($cursor, min($tokenFin, $fin));
            }

            $nuevo .= substr($completo, $cursor, $fin - $cursor);

            if ($nuevo !== $textos[$indice]) {
                while ($nodo->firstChild) {
                    $nodo->removeChild($nodo->firstChild);
                }

                $nodo->appendChild($nodo->ownerDocument->createTextNode($nuevo));
                $nodo->setAttribute('xml:space', 'preserve');
            }
        }
    }

    /**
     * Texto plano del DOCX: concatena las partes word/*.xml sin etiquetas y
     * con las entidades XML decodificadas (así `&lt;&lt;` vuelve a ser `<<`).
     */
    private function textoPlanoDelDocx(string $rutaAbsoluta): string
    {
        $zip = new ZipArchive;

        if ($zip->open($rutaAbsoluta, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException("No se pudo abrir la plantilla DOCX: {$rutaAbsoluta}");
        }

        $xml = '';

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $nombre = $zip->getNameIndex($i);

            if (preg_match('#^word/(document|header\d*|footer\d*)\.xml$#', $nombre)) {
                $xml .= $zip->getFromIndex($i);
            }
        }

        $zip->close();

        return html_entity_decode(
            preg_replace('/<[^>]+>/', '', $xml) ?? '',
            ENT_QUOTES | ENT_XML1,
            'UTF-8'
        );
    }
}
