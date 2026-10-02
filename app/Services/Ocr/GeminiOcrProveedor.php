<?php

namespace App\Services\Ocr;

use App\Core\Contracts\ProveedorOcr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiOcrProveedor implements ProveedorOcr
{
    public function extraer(string $rutaImagen): array
    {
        $apiKey = config('services.gemini.api_key');
        if (! $apiKey) {
            throw new RuntimeException('Falta configurar GEMINI_API_KEY');
        }

        $imageData = base64_encode(file_get_contents($rutaImagen));
        $mimeType = mime_content_type($rutaImagen) ?: 'image/jpeg';

        $prompt = <<<'EOT'
Extrae los siguientes datos de la carta adjunta y responde ÚNICAMENTE con un objeto JSON válido (sin formato markdown ni backticks).
El JSON debe tener exactamente esta estructura y los valores deben cumplir estas reglas:
{
  "carta_docente_numero": "Ej: 052-2026-FIME",
  "carta_docente_fecha": "YYYY-MM-DD",
  "titulo": "Título exacto del artículo",
  "revista": "Nombre de la revista",
  "base_indexadora": "Debe ser exactamente uno de: Scopus, Web of Science, SciELO, Otra",
  "cuartil": "Debe ser exactamente uno de: Q1, Q2, Q3, Q4",
  "monto_solicitado": número decimal (ej. 1500.50),
  "docente_dni": "8 dígitos numéricos",
  "docente_nombre": "Nombre completo del docente",
  "doi": "DOI del artículo, ej. 10.1234/abcd (sin URL)"
}
Si algún dato no se encuentra o es ilegible, envíalo como nulo (null).
EOT;

        $modelo = config('services.gemini.model', 'gemini-3.5-flash');

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(60)
            ->retry(3, 3000, fn ($exception, $pendingRequest) => in_array($exception->response?->status(), [429, 503]), throw: false)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $imageData,
                                ],
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                ],
            ]);

        if (! $response->successful()) {
            Log::error('Error de Gemini OCR', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('Error de conexión con Gemini OCR');
        }

        $jsonStr = $response->json('candidates.0.content.parts.0.text');
        if (! $jsonStr) {
            throw new RuntimeException('Gemini no devolvió texto');
        }

        // Clean markdown backticks if Gemini ignored the instruction
        $jsonStr = preg_replace('/^```json\s*/', '', $jsonStr);
        $jsonStr = preg_replace('/\s*```$/', '', $jsonStr);

        $datos = json_decode($jsonStr, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Respuesta OCR no es JSON válido');
        }

        // La API estándar de Gemini no devuelve confianza por campo: se reporta
        // alta cuando el dato se extrajo y cero cuando vino nulo.
        $confianza = [];
        foreach ($datos as $k => $v) {
            $confianza[$k] = $v !== null ? 0.95 : 0.0;
        }

        return [
            'datos' => $datos,
            'confianza' => $confianza,
        ];
    }
}
