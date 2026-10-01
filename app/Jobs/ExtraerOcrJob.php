<?php

namespace App\Jobs;

use App\Models\Archivo;
use App\Services\Ocr\GeminiOcrProveedor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ExtraerOcrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(public Archivo $archivo)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(GeminiOcrProveedor $ocr): void
    {
        try {
            $this->archivo->update(['ocr_estado' => 'PROCESANDO']);

            $path = Storage::path($this->archivo->ruta);

            // Convert PDF to image if necessary
            $imagePath = $path;
            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf') {
                $imagePath = $path . '.jpg';
                // Use ghostscript to extract first page (assuming gs is available)
                // Windows often has gs or pdftoppm.
                // We will try ghostscript. If not available, we fail gracefully.
                exec(sprintf(
                    'gswin64c -dSAFER -dBATCH -dNOPAUSE -sDEVICE=jpeg -r150 -dFirstPage=1 -dLastPage=1 -sOutputFile="%s" "%s"',
                    $imagePath,
                    $path
                ), $output, $returnCode);

                if ($returnCode !== 0) {
                    // Try without win64
                    exec(sprintf(
                        'gs -dSAFER -dBATCH -dNOPAUSE -sDEVICE=jpeg -r150 -dFirstPage=1 -dLastPage=1 -sOutputFile="%s" "%s"',
                        $imagePath,
                        $path
                    ), $output, $returnCode);
                    
                    if ($returnCode !== 0) {
                        throw new \RuntimeException('Error convirtiendo PDF a JPG con gs.');
                    }
                }
            }

            $resultado = $ocr->extraer($imagePath);

            $this->archivo->update([
                'ocr_estado' => 'COMPLETADO',
                'ocr_json' => $resultado['datos'],
                'ocr_confianza' => $resultado['confianza'],
            ]);

            if ($imagePath !== $path && file_exists($imagePath)) {
                unlink($imagePath);
            }

        } catch (\Throwable $e) {
            Log::error('Error en OCR', ['archivo' => $this->archivo->id, 'error' => $e->getMessage()]);
            $this->archivo->update([
                'ocr_estado' => 'ERROR',
                'ocr_json' => ['error' => $e->getMessage()]
            ]);
        }
    }
}
