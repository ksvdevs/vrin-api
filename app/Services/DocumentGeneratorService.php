<?php

namespace App\Services;

use App\Events\DocumentoGeneradoCreado;
use App\Exceptions\DomainException;
use App\Jobs\ConvertDocxToPdfJob;
use App\Models\DocumentoGenerado;
use App\Models\Expediente;
use App\Models\PlantillaSeleccionada;
use App\Models\TipoDocumentoPlantilla;
use App\Models\Usuario;
use Illuminate\Support\Str;

/**
 * Motor de generación documental (núcleo reutilizable de las Fases 6-7).
 *
 * Lee la plantilla vigente seleccionada (RN-13), arma el mapa token→valor
 * (defaults del expediente + datosExtra del ejecutor), verifica contra los
 * tokens indexados de la plantilla que nada quede sin dato (RN-09), produce
 * el DOCX con PhpWord (dos pasadas de delimitadores) y encola la conversión
 * a PDF. NO cambia el estado del expediente: eso lo hace el workflow.
 */
class DocumentGeneratorService
{
    /** Mapeo codigo tipo_documento => valor del ENUM documentos_generados.tipo. */
    private const TIPO_GENERADO = [
        'CARTA' => 'CARTA_VRIN',
        'RESOLUCION' => 'RESOLUCION',
    ];

    public function __construct(private readonly TokenParserService $parser) {}

    /**
     * @param  array<string, mixed>  $datosExtra
     */
    public function generar(Expediente $expediente, string $tipo, array $datosExtra, Usuario $actor): DocumentoGenerado
    {
        $tipoDocumento = TipoDocumentoPlantilla::where('codigo', $tipo)->first();

        if ($tipoDocumento === null) {
            throw new DomainException("Tipo de documento desconocido: {$tipo}.");
        }

        $seleccion = PlantillaSeleccionada::where('modulo', 'ARTICULOS')
            ->where('tipo_documento_id', $tipoDocumento->id)
            ->with('plantilla')
            ->first();

        $plantilla = $seleccion?->plantilla;

        if ($plantilla === null || $plantilla->estado !== 'ACTIVO') {
            throw new DomainException("No hay plantilla seleccionada para {$tipo} (RN-13).");
        }

        $mapa = $this->construirMapa($expediente, $tipo, $datosExtra);
        $tokens = $plantilla->tokens ?? [];

        foreach ($tokens as $token) {
            if (! array_key_exists($token, $mapa) || $mapa[$token] === null) {
                throw new DomainException("Token sin dato: {$token} (RN-09).");
            }
        }

        $tipoGenerado = self::TIPO_GENERADO[$tipo];
        $version = (int) DocumentoGenerado::where('expediente_id', $expediente->id)
            ->where('tipo', $tipoGenerado)
            ->max('version') + 1;

        $nombreBase = sprintf('%s_%d_%s', $tipoGenerado, $version, Str::lower(Str::random(8)));
        $directorio = "documentos/{$expediente->id}";
        if (! is_dir(storage_path("app/{$directorio}"))) {
            mkdir(storage_path("app/{$directorio}"), 0755, true);
        }
        $rutaTemporal = $this->rutaAbsoluta("{$directorio}/{$nombreBase}.tmp.docx");
        $rutaFinal = "{$directorio}/{$nombreBase}.docx";

        // Pasada 1: delimitadores << >> (cuerpo).
        PlantillaProcessor::fijarDelimitadores('<<', '>>');
        $procesador = new PlantillaProcessor($this->rutaAbsoluta($plantilla->archivo_path));
        $procesador->normalizarDelimitadores();

        foreach ($mapa as $clave => $valor) {
            $procesador->setValue($clave, $this->escapar($valor));
        }

        $procesador->saveAs($rutaTemporal);

        // Pasada 2: delimitadores {{ }} (membrete de la resolución).
        PlantillaProcessor::fijarDelimitadores('{{', '}}');
        $procesador2 = new PlantillaProcessor($rutaTemporal);
        $procesador2->normalizarDelimitadores();

        foreach ($mapa as $clave => $valor) {
            $procesador2->setValue($clave, $this->escapar($valor));
        }

        $procesador2->saveAs($this->rutaAbsoluta($rutaFinal));
        unlink($rutaTemporal);

        $this->validarDocx($this->rutaAbsoluta($rutaFinal));

        $documento = DocumentoGenerado::create([
            'expediente_id' => $expediente->id,
            'tipo' => $tipoGenerado,
            'version' => $version,
            'plantilla_id' => $plantilla->id,
            'datos' => $mapa,
            'docx_path' => $rutaFinal,
            'pdf_path' => null,
            'sha256' => hash_file('sha256', $this->rutaAbsoluta($rutaFinal)),
            'codigo_verificacion' => $this->generarCodigoVerificacion(),
            'es_vigente' => true,
            'generado_por' => $actor->id,
            'generado_at' => now(),
        ]);

        ConvertDocxToPdfJob::dispatch($documento->id);

        event(new DocumentoGeneradoCreado($documento, $actor));

        return $documento;
    }

    /**
     * Defaults desde el expediente (snapshot + relaciones) fusionados con los
     * datosExtra del ejecutor (estos ganan). Los tokens de etapas futuras
     * (FECHA_CARTA_VRIN, META, …) NO se inventan: deben venir en datosExtra.
     *
     * @param  array<string, mixed>  $datosExtra
     * @return array<string, mixed>
     */
    public function construirMapa(Expediente $expediente, string $tipo, array $datosExtra): array
    {
        $expediente->loadMissing(['docente', 'articulo', 'escuela']);

        $articulo = $expediente->articulo;

        $mapa = [
            'CIUDAD' => config('vrin.ciudad'),
            'GRADO' => $expediente->grado,
            'NOMBRES' => $expediente->docente?->nombres,
            'APELLIDO_PATERNO' => $expediente->docente?->apellido_paterno,
            'APELLIDO_MATERNO' => $expediente->docente?->apellido_materno ?? '',
            'CARTA_DOCENTE' => 'CARTA N° '.$expediente->carta_docente_numero,
            'FECHA_CARTA_DOCENTE' => $expediente->carta_docente_fecha?->format('d/m/Y'),
            'TITULO_ARTICULO' => $articulo?->titulo,
            'REVISTA' => $articulo?->revista,
            'BASE_DATOS' => $articulo?->base_indexadora,
            'CUARTIL' => $articulo?->cuartil,
            'MONTO_TOTAL_SOLICITADO' => $articulo !== null
                ? number_format((float) $articulo->monto_solicitado, 2, '.', ',')
                : null,
            'ESCUELA' => $expediente->escuela?->nombre,
            'REGLAMENTO_BASE' => config('vrin.reglamento_base'),
        ];

        return array_merge($mapa, $datosExtra);
    }

    private function generarCodigoVerificacion(): string
    {
        do {
            $codigo = Str::upper(Str::random(12));
        } while (DocumentoGenerado::where('codigo_verificacion', $codigo)->exists());

        return $codigo;
    }

    private function escapar(mixed $valor): string
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    /**
     * Defensa de calidad (RN-09): verifica que TODAS las partes XML del DOCX
     * resultante (cuerpo, encabezados y pies) estén bien formadas antes de
     * persistir la fila; si alguna parte quedó corrupta, aborta con un
     * mensaje claro en vez de dejar un documento que LibreOffice no cargue.
     */
    private function validarDocx(string $rutaAbsoluta): void
    {
        $zip = new \ZipArchive;

        if ($zip->open($rutaAbsoluta) !== true) {
            throw new \RuntimeException("No se pudo abrir el DOCX generado para validarlo: {$rutaAbsoluta}");
        }

        $errores = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $parte = $zip->getNameIndex($i);

            if (! is_string($parte) || ! preg_match('/^word\/(document|header|footer)\d*\.xml$/', $parte)) {
                continue;
            }

            $xml = $zip->getFromName($parte);

            if ($xml === false) {
                continue;
            }

            $documento = new \DOMDocument;
            $anterior = libxml_use_internal_errors(true);

            if (! $documento->loadXML($xml, LIBXML_NONET)) {
                $mensajes = array_map(
                    fn (\LibXMLError $e): string => trim($e->message),
                    libxml_get_errors()
                );
                $errores[] = "{$parte}: ".implode(' | ', array_slice($mensajes, 0, 2));
            }

            libxml_clear_errors();
            libxml_use_internal_errors($anterior);
        }

        $zip->close();

        if ($errores !== []) {
            throw new \RuntimeException(
                'El DOCX generado tiene XML mal formado ('.implode('; ', $errores).').'
            );
        }
    }

    private function rutaAbsoluta(string $relativa): string
    {
        return storage_path('app/'.$relativa);
    }
}
