<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ArticuloOcrTest extends TestCase
{
    private function usuario(): Usuario
    {
        $usuario = new Usuario;
        $usuario->id = 1;

        return $usuario;
    }

    private function fakeGeminiOk(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'carta_docente_numero' => '017-2026-CP-MVZ',
                                        'carta_docente_fecha' => '2026-07-14',
                                        'titulo' => 'Biopolymeric Films and Coatings',
                                        'revista' => 'Foods',
                                        'base_indexadora' => 'Scopus',
                                        'cuartil' => 'Q1',
                                        'monto_solicitado' => 11420.00,
                                        'docente_dni' => '41992310',
                                        'docente_nombre' => 'Luis Ricardo Paredes Quiroz',
                                        'doi' => null,
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);
    }

    public function test_extrae_metadatos_de_la_carta(): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        $this->fakeGeminiOk();
        $this->actingAs($this->usuario());

        $response = $this->postJson('/api/articulos/ocr', [
            'archivo' => UploadedFile::fake()->create('carta.pdf', 100, 'application/pdf'),
        ]);

        $response->assertOk()
            ->assertJsonPath('datos.carta_docente_numero', '017-2026-CP-MVZ')
            ->assertJsonPath('datos.base_indexadora', 'Scopus')
            ->assertJsonPath('nombre_archivo', 'carta.pdf')
            ->assertJsonPath('campos_extraidos', 9);
    }

    public function test_rechaza_archivo_invalido(): void
    {
        $this->actingAs($this->usuario());

        $response = $this->postJson('/api/articulos/ocr', [
            'archivo' => UploadedFile::fake()->create('carta.exe', 100),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['archivo']);
    }

    public function test_devuelve_422_si_gemini_falla(): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response('error', 500),
        ]);
        $this->actingAs($this->usuario());

        $response = $this->postJson('/api/articulos/ocr', [
            'archivo' => UploadedFile::fake()->create('carta.pdf', 100, 'application/pdf'),
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'No se pudo analizar la carta. Complete el formulario manualmente.');
    }

    public function test_requiere_autenticacion(): void
    {
        $response = $this->postJson('/api/articulos/ocr', [
            'archivo' => UploadedFile::fake()->create('carta.pdf', 100, 'application/pdf'),
        ]);

        $response->assertUnauthorized();
    }
}
