<?php

namespace App\Support;

/**
 * Tokens con mapeo de datos conocidos por tipo de documento (§Fases 6-7).
 * Lo que no esté aquí se reporta como «sin mapeo conocido» al subir la
 * plantilla (advertencia, no bloqueo) — p. ej. NOMBRE_JEFE_OPP y
 * CARGO_JEFE_OPP hasta que el usuario edite plantilla_carta.docx (D-10).
 */
final class TokensConocidos
{
    /** @var array<int, string> */
    public const CARTA = [
        'CIUDAD',
        'FECHA_CARTA_VRIN',
        'NUMERO_CARTA_VRIN',
        'GRADO',
        'NOMBRES',
        'APELLIDO_PATERNO',
        'APELLIDO_MATERNO',
        'CARTA_DOCENTE',
        'REGISTRO_MESA_PARTES',
        'MONTO_TOTAL_SOLICITADO',
        'TITULO_ARTICULO',
        'REVISTA',
        'BASE_DATOS',
        'CUARTIL',
    ];

    /** @var array<int, string> */
    public const RESOLUCION = [
        'numero_resolucion',
        'anio',
        'fecha_emision',
        'NUMERO_REGISTRO_VRIN',
        'FECHA_REGISTRO_VRIN',
        'TITULO_ARTICULO',
        'GRADO',
        'NOMBRES',
        'APELLIDO_PATERNO',
        'APELLIDO_MATERNO',
        'CARTA_OPP',
        'FECHA_CARTA_OPP',
        'CARTA_DOCENTE',
        'FECHA_CARTA_DOCENTE',
        'REVISTA',
        'BASE_DATOS',
        'CUARTIL',
        'MONTO_TOTAL_SOLICITADO',
        'META',
        'ESPECIFICA',
        'FUENTE',
        'MONTO_APROBADO',
        'ESCUELA',
        'REGLAMENTO_BASE',
    ];

    /**
     * @return array<int, string>
     */
    public static function conocidos(string $tipoCodigo): array
    {
        return match ($tipoCodigo) {
            'CARTA' => self::CARTA,
            'RESOLUCION' => self::RESOLUCION,
            default => [],
        };
    }

    /**
     * Tokens de la plantilla sin mapeo conocido para su tipo (ordenados).
     *
     * @param  array<int, string>  $tokens
     * @return array<int, string>
     */
    public static function sinMapeo(array $tokens, string $tipoCodigo): array
    {
        $sinMapeo = array_diff($tokens, self::conocidos($tipoCodigo));
        sort($sinMapeo);

        return array_values($sinMapeo);
    }
}
