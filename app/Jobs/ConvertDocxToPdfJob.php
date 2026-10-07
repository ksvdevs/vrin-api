<?php

namespace App\Jobs;

use App\Models\DocumentoGenerado;
use App\Services\ConvertidorPdfService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Convierte el DOCX generado a PDF con LibreOffice headless (D-23).
 * La ruta del binario es configurable: config('vrin.soffice_path').
 */
class ConvertDocxToPdfJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 120;

    public function __construct(public readonly int $documentoGeneradoId) {}

    public function handle(): void
    {
        $documento = DocumentoGenerado::find($this->documentoGeneradoId);

        if ($documento === null || $documento->pdf_path !== null) {
            return;
        }

        $docx = storage_path('app/'.$documento->docx_path);

        if (! is_file($docx)) {
            throw new \RuntimeException("El DOCX no existe en disco: {$documento->docx_path}");
        }

        app(ConvertidorPdfService::class)->convertir($docx);

        $documento->pdf_path = substr($documento->docx_path, 0, -strlen('.docx')).'.pdf';
        $documento->save();
    }
}
