<?php

namespace App\Http\Controllers;

use App\Services\Ocr\GeminiOcrProveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class OcrController extends Controller
{
    public function extraer(Request $request, GeminiOcrProveedor $ocr)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:25600',
        ]);

        $file = $request->file('archivo');
        $path = $file->storeAs('ocr_temp', Str::uuid().'.'.$file->extension());

        try {
            $resultado = $ocr->extraer(Storage::path($path));

            $camposExtraidos = collect($resultado['datos'])
                ->filter(fn ($valor) => $valor !== null && $valor !== '')
                ->count();

            return response()->json([
                'datos' => $resultado['datos'],
                'confianza' => $resultado['confianza'],
                'campos_extraidos' => $camposExtraidos,
                'nombre_archivo' => $file->getClientOriginalName(),
            ]);
        } catch (Throwable $e) {
            Log::error('Error en OCR de carta docente', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'No se pudo analizar la carta. Complete el formulario manualmente.',
            ], 422);
        } finally {
            Storage::delete($path);
        }
    }
}
