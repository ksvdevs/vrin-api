<?php

namespace App\Http\Controllers;

use App\Jobs\ExtraerOcrJob;
use App\Models\Archivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OcrController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:25600',
        ]);

        $file = $request->file('archivo');
        $path = $file->storeAs('ocr_temp', Str::uuid() . '.' . $file->extension());

        $archivo = Archivo::create([
            'expediente_id' => null, // Not yet linked
            'nombre_original' => $file->getClientOriginalName(),
            'ruta' => $path,
            'sha256' => hash_file('sha256', $file->getRealPath()),
            'tamano_bytes' => $file->getSize(),
            'tipo' => 'CARTA_DOCENTE',
            'etapa' => 1,
            'subido_por' => $request->user()->id,
            'ocr_estado' => 'PENDIENTE'
        ]);

        ExtraerOcrJob::dispatch($archivo);

        return response()->json(['archivo_id' => $archivo->id], 202);
    }

    public function status($id, Request $request)
    {
        $archivo = Archivo::where('subido_por', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'id' => $archivo->id,
            'ocr_estado' => $archivo->ocr_estado,
            'ocr_json' => $archivo->ocr_json,
            'ocr_confianza' => $archivo->ocr_confianza,
        ]);
    }
}
