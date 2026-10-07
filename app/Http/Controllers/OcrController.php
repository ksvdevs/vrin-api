<?php

namespace App\Http\Controllers;

use App\Models\Expediente;
use App\Services\Ocr\GeminiOcrProveedor;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class OcrController extends Controller
{
    use AuthorizesRequests;

    public function extraerCartaOpp(Request $request, Expediente $expediente, GeminiOcrProveedor $ocr): JsonResponse
    {
        $this->authorize('registrarOpp', $expediente);
        abort_unless($expediente->estado === 'EN_ESPERA_OPP', 409, 'La respuesta OPP ya fue registrada.');
        $request->validate(['archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:25600']);

        $archivo = $request->file('archivo');
        $ruta = $archivo->storeAs('ocr_temp', Str::uuid().'.'.$archivo->extension());

        try {
            $resultado = $ocr->extraerCartaOpp(Storage::path($ruta));
            $claves = ['disponibilidad', 'monto_aprobado', 'meta_presupuestal', 'especifica_gasto', 'fuente_financiamiento', 'carta_numero', 'carta_fecha', 'registro_vrin_numero', 'registro_vrin_fecha'];
            $datos = array_fill_keys($claves, null);
            foreach ($claves as $clave) {
                $datos[$clave] = $resultado['datos'][$clave] ?? null;
            }

            if (count(array_filter($datos, fn ($valor) => $valor !== null && $valor !== '')) === 0) {
                return response()->json(['message' => 'No se identificaron datos legibles. Revise la carta o complete el formulario manualmente.'], 422);
            }

            return response()->json(['datos' => $datos, 'confianza' => $resultado['confianza'] ?? [], 'nombre_archivo' => $archivo->getClientOriginalName()]);
        } catch (ConnectionException $e) {
            Log::warning('Servicio OCR OPP inaccesible', ['excepcion' => $e::class]);

            return response()->json(['message' => 'No se pudo conectar con el servicio de análisis. Complete los datos manualmente o inténtelo de nuevo.'], 503);
        } catch (Throwable $e) {
            Log::error('Error en OCR de carta OPP', ['excepcion' => $e::class]);

            return response()->json(['message' => 'No se pudo analizar la carta OPP. Complete el formulario manualmente.'], 422);
        } finally {
            Storage::delete($ruta);
        }
    }

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

            if ($camposExtraidos === 0) {
                return response()->json([
                    'message' => 'No se identificaron datos legibles en la carta. Verifique el PDF o complete el formulario manualmente.',
                ], 422);
            }

            return response()->json([
                'datos' => $resultado['datos'],
                'confianza' => $resultado['confianza'],
                'campos_extraidos' => $camposExtraidos,
                'nombre_archivo' => $file->getClientOriginalName(),
            ]);
        } catch (ConnectionException $e) {
            Log::warning('Servicio OCR inaccesible', ['excepcion' => $e::class]);

            return response()->json([
                'message' => 'No se pudo conectar con el servicio de análisis. Verifique la conexión del servidor e inténtelo de nuevo.',
            ], 503);
        } catch (Throwable $e) {
            Log::error('Error en OCR de carta docente', ['excepcion' => $e::class]);

            return response()->json([
                'message' => 'No se pudo analizar la carta. Complete el formulario manualmente.',
            ], 422);
        } finally {
            Storage::delete($path);
        }
    }
}
