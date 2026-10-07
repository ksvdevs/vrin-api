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

        if ($documento === null) {
            return;
        }

        app(ConvertidorPdfService::class)->asegurarDisponible($documento);
    }
}
