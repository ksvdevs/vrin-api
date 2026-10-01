<?php

namespace App\Jobs;

use App\Models\DocumentoGenerado;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Conversión DOCX→PDF con LibreOffice headless (D-15, D-23).
 *
 * Requiere el binario `soffice` accesible (ruta en `vrin.libreoffice_bin`,
 * env VRIN_LIBREOFFICE_BIN). Si la conversión falla, el job queda en
 * `failed_jobs` y el error queda en el log; el DOCX y su fila en
 * `documentos_generados` no se tocan.
 */
class ConvertDocxToPdfJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 180;

    public function __construct(public int $documentoGeneradoId) {}

    public function handle(): void
    {
        $documento = DocumentoGenerado::find($this->documentoGeneradoId);

        if (! $documento) {
            return;
        }

        $binario = (string) config('vrin.libreoffice_bin', 'soffice');
        $rutaDocx = Storage::disk('local')->path($documento->docx_path);
        $directorio = dirname($rutaDocx);

        $proceso = new Process([
            $binario,
            '--headless',
            '--convert-to',
            'pdf',
            '--outdir',
            $directorio,
            $rutaDocx,
        ]);
        $proceso->setTimeout($this->timeout - 30);
        $proceso->run();

        if (! $proceso->isSuccessful()) {
            Log::error('ConvertDocxToPdfJob: falló la conversión a PDF', [
                'documento_generado_id' => $documento->id,
                'binario' => $binario,
                'salida' => $proceso->getOutput(),
                'error' => $proceso->getErrorOutput(),
            ]);

            throw new RuntimeException(
                "No se pudo convertir a PDF el documento {$documento->id}. "
                .'Verifique que LibreOffice esté instalado y que VRIN_LIBREOFFICE_BIN apunte a soffice.'
            );
        }

        $rutaPdf = preg_replace('/\.docx$/i', '.pdf', $rutaDocx);

        if (! is_string($rutaPdf) || ! file_exists($rutaPdf)) {
            Log::error('ConvertDocxToPdfJob: soffice terminó sin error pero no produjo el PDF', [
                'documento_generado_id' => $documento->id,
                'esperado' => $rutaPdf,
            ]);

            throw new RuntimeException("La conversión no produjo el PDF esperado para el documento {$documento->id}.");
        }

        $documento->forceFill([
            'pdf_path' => preg_replace('/\.docx$/i', '.pdf', $documento->docx_path),
        ])->save();
    }
}
