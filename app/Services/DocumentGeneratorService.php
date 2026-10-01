<?php

namespace App\Services;

use App\Events\DocumentoGenerado as DocumentoGeneradoEvento;
use App\Exceptions\DomainException;
use App\Jobs\ConvertDocxToPdfJob;
use App\Models\DocumentoGenerado;
use App\Models\Expediente;
use App\Models\Plantilla;
use App\Models\PlantillaSeleccionada;
use App\Models\TipoDocumentoPlantilla;
use App\Models\Usuario;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

/**
 * Motor de generación documental (Fase 5, D-15, RN-09, RN-13).
 *
 * El mapa token→valor se arma con (a) un resolvedor por tipo de documento —
 * punto de extensión que las Fases 6 (CARTA) y 7 (RESOLUCION) completarán con
 * los valores reales del expediente vía `registrarResolvedor()` — y (b) los
 * `datosExtra` del llamador, que prevalecen sobre el resolvedor.
 *
 * La transición de estado del expediente NO vive aquí: la hace el workflow
 * que invoca este servicio, dentro de su propia transacción.
 */
class DocumentGeneratorService
{
    /**
     * Resolvedores por tipo de documento: callable(Expediente, array<string, string>): array<string, string>.
     *
     * @var array<string, callable(Expediente, array<string, string>): array<string, string>>
     */
    private array $resolvedores = [];

    public function __construct(private TokenParserService $tokens) {}

    /**
     * Registra el resolvedor de tokens de un tipo de documento (Fases 6/7).
     *
     * @param  callable(Expediente, array<string, string>): array<string, string>  $resolvedor
     */
    public function registrarResolvedor(string $tipoDocumento, callable $resolvedor): void
    {
        $this->resolvedores[$tipoDocumento] = $resolvedor;
    }

    /**
     * Genera una nueva versión del documento del expediente desde la plantilla
     * vigente. Jamás sobreescribe versiones anteriores (RN-02/RNF-06).
     *
     * @param  string  $tipoDocumento  CARTA|RESOLUCION (código de tipos_documento_plantilla)
     * @param  array<string, string>  $datosExtra
     *
     * @throws DomainException si no hay plantilla vigente o falta el valor de un token
     */
    public function generar(Expediente $expediente, string $tipoDocumento, array $datosExtra, Usuario $actor): DocumentoGenerado
    {
        $plantilla = $this->plantillaVigente($expediente, $tipoDocumento);

        $mapa = $this->armarMapa($expediente, $tipoDocumento, $datosExtra);

        $rutaTemporal = tempnam(sys_get_temp_dir(), 'sgr_tpl_');

        try {
            // Copia normalizada: <<TOKEN>>, << TOKEN>> y {{ token }} → ${TOKEN}
            // (la plantilla almacenada es inmutable).
            $this->tokens->normalizarCopia(
                Storage::disk('local')->path($plantilla->archivo_path),
                $rutaTemporal
            );

            $procesador = new TemplateProcessor($rutaTemporal);
            $variables = $procesador->getVariables();

            // RN-09: bloquear la emisión si algún token queda sin valor.
            $faltantes = array_values(array_filter(
                $variables,
                fn (string $variable) => ! array_key_exists($variable, $mapa)
                    || $mapa[$variable] === null
                    || $mapa[$variable] === ''
            ));

            if ($faltantes !== []) {
                throw new DomainException(
                    'No se puede generar el documento: falta valor para el token «'
                    .implode('», «', $faltantes).'».'
                );
            }

            foreach ($variables as $variable) {
                $procesador->setValue($variable, (string) $mapa[$variable]);
            }

            $tipoColumna = $tipoDocumento === 'CARTA' ? 'CARTA_VRIN' : 'RESOLUCION';
            $version = (int) DocumentoGenerado::where('expediente_id', $expediente->id)
                ->where('tipo', $tipoColumna)
                ->lockForUpdate()
                ->max('version') + 1;

            $rutaRelativa = "documentos/{$expediente->id}/{$tipoColumna}_v{$version}.docx";
            $rutaFinal = Storage::disk('local')->path($rutaRelativa);
            Storage::disk('local')->makeDirectory("documentos/{$expediente->id}");

            $procesador->saveAs($rutaFinal);

            // Red de seguridad: el DOCX emitido no puede conservar tokens.
            $residuales = $this->tokens->extraerTokens($rutaFinal);

            if ($residuales !== []) {
                Storage::disk('local')->delete($rutaRelativa);

                throw new DomainException(
                    'El documento generado conserva tokens sin reemplazar: «'
                    .implode('», «', $residuales).'».'
                );
            }

            $documento = DocumentoGenerado::create([
                'expediente_id' => $expediente->id,
                'tipo' => $tipoColumna,
                'version' => $version,
                'plantilla_id' => $plantilla->id,
                'datos' => array_intersect_key($mapa, array_flip($variables)),
                'docx_path' => $rutaRelativa,
                'pdf_path' => null,
                'sha256' => hash_file('sha256', $rutaFinal),
                'es_vigente' => true,
                'generado_por' => $actor->id,
                'generado_at' => now(),
            ]);
        } finally {
            @unlink($rutaTemporal);
        }

        DocumentoGeneradoEvento::dispatch($documento, $expediente, $actor);
        ConvertDocxToPdfJob::dispatch($documento->id);

        return $documento;
    }

    /**
     * Plantilla vigente para el módulo del expediente y el tipo de documento
     * (RN-13: sin selección, la generación queda bloqueada).
     */
    private function plantillaVigente(Expediente $expediente, string $tipoDocumento): Plantilla
    {
        $tipo = TipoDocumentoPlantilla::where('codigo', $tipoDocumento)->first();

        if (! $tipo) {
            throw new DomainException("Tipo de documento desconocido: {$tipoDocumento}.");
        }

        $seleccion = PlantillaSeleccionada::with('plantilla')
            ->where('modulo', $expediente->modulo)
            ->where('tipo_documento_id', $tipo->id)
            ->first();

        if (! $seleccion?->plantilla) {
            throw new DomainException(
                "No hay plantilla seleccionada para {$tipoDocumento} en el módulo {$expediente->modulo}."
            );
        }

        if ($seleccion->plantilla->estado !== 'ACTIVO') {
            throw new DomainException(
                "La plantilla seleccionada para {$tipoDocumento} ({$seleccion->plantilla->codigo}) está INACTIVA."
            );
        }

        return $seleccion->plantilla;
    }

    /**
     * Mapa token→valor: resolvedor del tipo (Fases 6/7) + datosExtra del
     * llamador, que prevalecen.
     *
     * @param  array<string, string>  $datosExtra
     * @return array<string, string>
     */
    private function armarMapa(Expediente $expediente, string $tipoDocumento, array $datosExtra): array
    {
        $resolvedor = $this->resolvedores[$tipoDocumento] ?? null;

        $delResolvedor = $resolvedor ? $resolvedor($expediente, $datosExtra) : [];

        return array_merge($delResolvedor, $datosExtra);
    }
}
