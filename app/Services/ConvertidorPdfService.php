<?php

namespace App\Services;

use App\Exceptions\DomainException;
use App\Models\DocumentoGenerado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ExceptionInterface;
use Symfony\Component\Process\Process;

class ConvertidorPdfService
{
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
            if (! is_file($archivoPdf)) {
                $this->convertir($rutaDocx);
            }

            if ($actual->pdf_path !== $rutaPdf) {
                $actual->pdf_path = $rutaPdf;
                $actual->save();
            }

            return $archivoPdf;
        });
    }

    public function convertir(string $docx): string
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
        // En HTTP, Symfony puede heredar solo las variables presentes en $_SERVER.
        // Conservamos el entorno del sistema sin propagar la configuración de Laravel.
        $entorno = getenv() + array_fill_keys(array_keys($_ENV), false);
        $proceso = new Process([$ejecutable, '-env:UserInstallation='.$uriPerfil, '--headless', '--nologo', '--nofirststartwizard', '--convert-to', 'pdf:writer_pdf_Export', '--outdir', dirname($docx), $docx], null, $entorno);
        $proceso->setTimeout(120);
        try {
            $proceso->run();
        } catch (ExceptionInterface $error) {
            throw new DomainException('No se pudo iniciar LibreOffice. Configura SOFFICE_PATH con la ruta del ejecutable.', previous: $error);
        } finally {
            File::deleteDirectory($perfil);
        }
        $pdf = substr($docx, 0, -strlen('.docx')).'.pdf';
        if (! $proceso->isSuccessful() || ! is_file($pdf)) {
            Log::warning('Falló la conversión de carta a PDF', ['salida' => $proceso->getOutput(), 'error' => $proceso->getErrorOutput(), 'codigo' => $proceso->getExitCode()]);
            throw new DomainException('No se pudo preparar el PDF de la plantilla. Verifica la configuración de LibreOffice.');
        }

        return $pdf;
    }
}
