<?php

namespace App\Services;

use App\Exceptions\DomainException;
use DOMDocument;
use DOMElement;
use DOMXPath;
use ZipArchive;

class FormatoResolucionService
{
    private const WORD_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    /** @param array<string, mixed> $datos */
    public function aplicar(string $rutaDocx, array $datos): void
    {
        $zip = new ZipArchive;
        if ($zip->open($rutaDocx) !== true) {
            throw new DomainException('No se pudo aplicar el formato de la resolución.');
        }

        try {
            $xml = $zip->getFromName('word/document.xml');
            if ($xml === false) {
                throw new DomainException('La resolución no contiene el cuerpo del documento.');
            }

            $documento = new DOMDocument;
            if (! $documento->loadXML($xml, LIBXML_NONET)) {
                throw new DomainException('El cuerpo de la resolución tiene un formato inválido.');
            }

            $xpath = new DOMXPath($documento);
            $xpath->registerNamespace('w', self::WORD_NS);
            $nombreDocente = trim(implode(' ', array_filter([
                $datos['GRADO'] ?? '',
                $datos['NOMBRES'] ?? '',
                $datos['APELLIDO_PATERNO'] ?? '',
                $datos['APELLIDO_MATERNO'] ?? '',
            ], fn (mixed $parte): bool => trim((string) $parte) !== '')));
            $tituloArticulo = trim((string) ($datos['TITULO_ARTICULO'] ?? ''));
            $cambio = false;

            foreach ($xpath->query('//w:body/w:p') as $parrafo) {
                $texto = $this->textoDelParrafo($xpath, $parrafo);
                if (str_starts_with($texto, 'El expediente') && $nombreDocente !== '') {
                    $cambio = $this->resaltar($documento, $xpath, $parrafo, $nombreDocente) || $cambio;
                } elseif (str_contains($texto, 'ARTICULO PRIMERO') && str_contains($texto, 'APROBAR') && $tituloArticulo !== '') {
                    $cambio = $this->resaltar($documento, $xpath, $parrafo, $tituloArticulo) || $cambio;
                    if ($nombreDocente !== '') {
                        $cambio = $this->resaltar($documento, $xpath, $parrafo, $nombreDocente) || $cambio;
                    }
                }
            }

            if ($cambio && ! $zip->addFromString('word/document.xml', $documento->saveXML())) {
                throw new DomainException('No se pudo guardar el formato de la resolución.');
            }
        } finally {
            $zip->close();
        }
    }

    private function textoDelParrafo(DOMXPath $xpath, DOMElement $parrafo): string
    {
        $texto = '';
        foreach ($xpath->query('./w:r/w:t', $parrafo) as $nodo) {
            $texto .= $nodo->textContent;
        }

        return $texto;
    }

    private function resaltar(DOMDocument $documento, DOMXPath $xpath, DOMElement $parrafo, string $frase): bool
    {
        $inicio = mb_strpos($this->textoDelParrafo($xpath, $parrafo), $frase);
        if ($inicio === false) {
            return false;
        }

        $fin = $inicio + mb_strlen($frase);
        $posicion = 0;
        foreach (iterator_to_array($xpath->query('./w:r/w:t', $parrafo)) as $nodoTexto) {
            $texto = $nodoTexto->textContent;
            $longitud = mb_strlen($texto);
            $inicioLocal = max(0, $inicio - $posicion);
            $finLocal = min($longitud, $fin - $posicion);
            $posicion += $longitud;

            if ($inicioLocal >= $finLocal) {
                continue;
            }

            $run = $nodoTexto->parentNode;
            if ($inicioLocal === 0 && $finLocal === $longitud) {
                $this->ponerNegrita($documento, $xpath, $run);

                continue;
            }

            $segmentos = [
                [mb_substr($texto, 0, $inicioLocal), false],
                [mb_substr($texto, $inicioLocal, $finLocal - $inicioLocal), true],
                [mb_substr($texto, $finLocal), false],
            ];
            foreach ($segmentos as [$contenido, $negrita]) {
                if ($contenido === '') {
                    continue;
                }
                $copia = $run->cloneNode(true);
                $copiaTexto = $xpath->query('./w:t', $copia)->item(0);
                $copiaTexto->nodeValue = $contenido;
                $copiaTexto->setAttribute('xml:space', 'preserve');
                if ($negrita) {
                    $this->ponerNegrita($documento, $xpath, $copia);
                }
                $run->parentNode->insertBefore($copia, $run);
            }
            $run->parentNode->removeChild($run);
        }

        return true;
    }

    private function ponerNegrita(DOMDocument $documento, DOMXPath $xpath, DOMElement $run): void
    {
        $propiedades = $xpath->query('./w:rPr', $run)->item(0);
        if (! $propiedades instanceof DOMElement) {
            $propiedades = $documento->createElementNS(self::WORD_NS, 'w:rPr');
            $run->insertBefore($propiedades, $run->firstChild);
        }

        $negrita = $xpath->query('./w:b', $propiedades)->item(0);
        if (! $negrita instanceof DOMElement) {
            $negrita = $documento->createElementNS(self::WORD_NS, 'w:b');
            $propiedades->insertBefore($negrita, $propiedades->firstChild);
        }
        $negrita->setAttributeNS(self::WORD_NS, 'w:val', '1');
    }
}
