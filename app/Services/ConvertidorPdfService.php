<?php

namespace App\Services;

use App\Exceptions\DomainException;
use App\Models\DocumentoGenerado;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ExceptionInterface;
use Symfony\Component\Process\Process;
use ZipArchive;

class ConvertidorPdfService
{
    private const WORD_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    public function asegurarDisponible(DocumentoGenerado $documento): string
    {
        return DB::transaction(function () use ($documento): string {
            $actual = DocumentoGenerado::whereKey($documento->id)->lockForUpdate()->firstOrFail();
            $rutaDocx = storage_path('app/'.$actual->docx_path);
            if (! is_file($rutaDocx)) {
                throw new DomainException('No se encuentra el documento Word de esta versión.');
            }

            $rutaPdf = substr($actual->docx_path, 0, -strlen('.docx')).'.pdf';
            $archivoPdf = storage_path('app/'.$rutaPdf);
            if (! is_file($archivoPdf)
                || (in_array($actual->tipo, ['CARTA_VRIN', 'RESOLUCION'], true)
                    && ! is_file($archivoPdf.'.formato-v5')
                    && ($this->requiereAjusteMembrete($rutaDocx)
                        || ($actual->tipo === 'RESOLUCION' && ! empty($actual->datos['TITULO_ARTICULO']))))) {
                $this->convertir($rutaDocx, $actual->tipo === 'RESOLUCION' ? $actual->datos : null);
            }

            if ($actual->pdf_path !== $rutaPdf) {
                $actual->pdf_path = $rutaPdf;
                $actual->save();
            }

            return $archivoPdf;
        });
    }

    /** @param array<string, mixed>|null $datosResolucion */
    public function convertir(string $docx, ?array $datosResolucion = null): string
    {
        $rutaReal = realpath($docx);
        if ($rutaReal === false || ! is_file($rutaReal)) {
            throw new DomainException('No se encuentra el documento Word preparado para la vista previa.');
        }
        $docx = $rutaReal;
        $ejecutable = config('vrin.soffice_path');
        if (PHP_OS_FAMILY === 'Windows' && str_ends_with(strtolower($ejecutable), '.exe')) {
            $consola = substr($ejecutable, 0, -4).'.com';
            if (is_file($consola)) {
                $ejecutable = $consola;
            }
        }
        $perfil = sys_get_temp_dir().'/vrin-lo-'.Str::random(12);
        File::ensureDirectoryExists($perfil);
        $uriPerfil = 'file://'.(PHP_OS_FAMILY === 'Windows' ? '/' : '').str_replace(['\\', ' '], ['/', '%20'], $perfil);
        try {
            $ajustarMembrete = $this->requiereAjusteMembrete($docx);
            $prepararCopia = $ajustarMembrete || $datosResolucion !== null;
            $fuente = $docx;
            $directorioSalida = dirname($docx);
            if ($prepararCopia) {
                $directorioFuente = $perfil.'/fuente';
                $directorioSalida = $perfil.'/salida';
                File::ensureDirectoryExists($directorioFuente);
                File::ensureDirectoryExists($directorioSalida);
                $fuente = $directorioFuente.'/'.basename($docx);
                if (! File::copy($docx, $fuente)) {
                    throw new DomainException('No se pudo preparar la carta para su conversión a PDF.');
                }
                if ($ajustarMembrete) {
                    $this->ajustarMembrete($fuente);
                }
                if ($datosResolucion !== null) {
                    app(FormatoResolucionService::class)->aplicar($fuente, $datosResolucion);
                }
            }

            // En HTTP, Symfony puede heredar solo las variables presentes en $_SERVER.
            // Conservamos el entorno del sistema sin propagar la configuración de Laravel.
            $entorno = getenv() + array_fill_keys(array_keys($_ENV), false);
            $proceso = new Process([$ejecutable, '-env:UserInstallation='.$uriPerfil, '--headless', '--nologo', '--nofirststartwizard', '--convert-to', 'pdf:writer_pdf_Export', '--outdir', $directorioSalida, $fuente], null, $entorno);
            $proceso->setTimeout(120);
            try {
                $proceso->run();
            } catch (ExceptionInterface $error) {
                throw new DomainException('No se pudo iniciar LibreOffice. Configura SOFFICE_PATH con la ruta del ejecutable.', previous: $error);
            }

            $pdfGenerado = $directorioSalida.'/'.substr(basename($docx), 0, -strlen('.docx')).'.pdf';
            if (! $proceso->isSuccessful() || ! is_file($pdfGenerado)) {
                Log::warning('Falló la conversión de carta a PDF', ['salida' => $proceso->getOutput(), 'error' => $proceso->getErrorOutput(), 'codigo' => $proceso->getExitCode()]);
                throw new DomainException('No se pudo preparar el PDF de la plantilla. Verifica la configuración de LibreOffice.');
            }

            $pdf = substr($docx, 0, -strlen('.docx')).'.pdf';
            if ($prepararCopia) {
                if (! File::copy($pdfGenerado, $pdf)) {
                    throw new DomainException('No se pudo guardar el PDF corregido de la carta.');
                }
                File::put($pdf.'.formato-v5', '');
            }

            return $pdf;
        } finally {
            File::deleteDirectory($perfil);
        }
    }

    private function requiereAjusteMembrete(string $docx): bool
    {
        $zip = new ZipArchive;
        if ($zip->open($docx, ZipArchive::RDONLY) !== true) {
            return false;
        }

        try {
            $contenido = $zip->getFromName('word/document.xml');
            if ($contenido === false) {
                return false;
            }
            $documento = new DOMDocument;
            if (! $documento->loadXML($contenido, LIBXML_NONET)) {
                return false;
            }
            $xpath = new DOMXPath($documento);
            $xpath->registerNamespace('w', self::WORD_NS);
            $margen = $xpath->query('//w:sectPr/w:pgMar')->item(0);
            if (! $margen instanceof DOMElement
                || (int) $margen->getAttributeNS(self::WORD_NS, 'top') >= 1200
                || (int) $margen->getAttributeNS(self::WORD_NS, 'header') > 400) {
                return false;
            }

            for ($indice = 0; $indice < $zip->numFiles; $indice++) {
                $nombre = $zip->getNameIndex($indice);
                if (! is_string($nombre) || ! preg_match('/^word\/header\d+\.xml$/', $nombre)) {
                    continue;
                }
                $contenidoCabecera = $zip->getFromName($nombre);
                if ($contenidoCabecera === false) {
                    continue;
                }
                $cabecera = new DOMDocument;
                if (! $cabecera->loadXML($contenidoCabecera, LIBXML_NONET)) {
                    continue;
                }
                $xpathCabecera = new DOMXPath($cabecera);
                $xpathCabecera->registerNamespace('w', self::WORD_NS);
                $xpathCabecera->registerNamespace('wp', 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing');
                if ($xpathCabecera->query('//wp:anchor')->length >= 2 && $this->parrafoCentralCabecera($xpathCabecera) !== null) {
                    return true;
                }
            }

            return false;
        } finally {
            $zip->close();
        }
    }

    private function ajustarMembrete(string $docx): void
    {
        $zip = new ZipArchive;
        if ($zip->open($docx) !== true) {
            throw new DomainException('No se pudo ajustar el membrete de la carta para PDF.');
        }

        try {
            $documento = new DOMDocument;
            $documento->loadXML($zip->getFromName('word/document.xml'), LIBXML_NONET);
            $xpath = new DOMXPath($documento);
            $xpath->registerNamespace('w', self::WORD_NS);
            $margen = $xpath->query('//w:sectPr/w:pgMar')->item(0);
            $margen->setAttributeNS(self::WORD_NS, 'w:top', '1800');
            $zip->addFromString('word/document.xml', $documento->saveXML());

            for ($indice = 0; $indice < $zip->numFiles; $indice++) {
                $nombre = $zip->getNameIndex($indice);
                if (! is_string($nombre) || ! preg_match('/^word\/header\d+\.xml$/', $nombre)) {
                    continue;
                }
                $contenido = $zip->getFromName($nombre);
                if ($contenido === false) {
                    continue;
                }
                $cabecera = new DOMDocument;
                $cabecera->loadXML($contenido, LIBXML_NONET);
                $xpathCabecera = new DOMXPath($cabecera);
                $xpathCabecera->registerNamespace('w', self::WORD_NS);
                $xpathCabecera->registerNamespace('wp', 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing');
                foreach ($xpathCabecera->query('//wp:anchor[wp:extent[@cy="0" and number(@cx) > 4000000]]/wp:positionV/wp:posOffset') as $posicionLinea) {
                    $posicionLinea->nodeValue = (string) ((int) $posicionLinea->nodeValue + 685800);
                }
                $parrafo = $this->parrafoTituloResolucion($xpathCabecera) ?? $this->parrafoCentralCabecera($xpathCabecera);
                if ($parrafo === null) {
                    continue;
                }
                $propiedades = $xpathCabecera->query('./w:pPr', $parrafo)->item(0);
                if (! $propiedades instanceof DOMElement) {
                    $propiedades = $cabecera->createElementNS(self::WORD_NS, 'w:pPr');
                    $parrafo->insertBefore($propiedades, $parrafo->firstChild);
                }
                $espaciado = $xpathCabecera->query('./w:spacing', $propiedades)->item(0);
                if (! $espaciado instanceof DOMElement) {
                    $espaciado = $cabecera->createElementNS(self::WORD_NS, 'w:spacing');
                    $propiedades->appendChild($espaciado);
                }
                $esResolucion = str_contains($parrafo->textContent, 'RESOLUCIÓN');
                $espaciado->setAttributeNS(self::WORD_NS, 'w:before', $esResolucion ? '1000' : '1200');
                if ($esResolucion) {
                    $alineacion = $cabecera->createElementNS(self::WORD_NS, 'w:jc');
                    $alineacion->setAttributeNS(self::WORD_NS, 'w:val', 'center');
                    $propiedades->appendChild($alineacion);
                    foreach ($xpathCabecera->query('.//w:t', $parrafo) as $texto) {
                        $texto->nodeValue = ltrim($texto->nodeValue);
                        if ($texto->nodeValue !== '') {
                            break;
                        }
                    }
                }
                $zip->addFromString($nombre, $cabecera->saveXML());
            }
        } finally {
            $zip->close();
        }
    }

    private function parrafoCentralCabecera(DOMXPath $xpath): ?DOMElement
    {
        foreach ($xpath->query('//w:p[w:pPr/w:jc[@w:val="center"]]') as $parrafo) {
            if ($parrafo instanceof DOMElement && mb_strlen(trim($parrafo->textContent)) > 20) {
                return $parrafo;
            }
        }

        return null;
    }

    private function parrafoTituloResolucion(DOMXPath $xpath): ?DOMElement
    {
        foreach ($xpath->query('//w:p') as $parrafo) {
            if ($parrafo instanceof DOMElement && str_contains($parrafo->textContent, 'RESOLUCIÓN N°')) {
                return $parrafo;
            }
        }

        return null;
    }
}
