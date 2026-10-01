<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Parámetros del SGR-VRIN (D-18)
    |--------------------------------------------------------------------------
    | Sin tabla `parametros`: los valores de negocio estables viven aquí.
    */

    // RN-04: plazo de rendición en días hábiles (lunes a viernes, sin feriados, D-19)
    'plazo_dias' => 60,

    // Ciudad por defecto de los documentos emitidos (<<CIUDAD>>)
    'ciudad' => 'Abancay',

    // Reglamento base citado en la resolución (<<REGLAMENTO_BASE>>)
    'reglamento_base' => 'Resolución N° 411-2026-R-UNAMBA',

    /*
    |--------------------------------------------------------------------------
    | Motor documental (D-15, RF-43)
    |--------------------------------------------------------------------------
    */

    // Binario de LibreOffice para el job DOCX→PDF. En Windows/Laragon definir
    // VRIN_LIBREOFFICE_BIN en .env con la ruta completa a soffice.exe.
    'libreoffice_bin' => env('VRIN_LIBREOFFICE_BIN', 'soffice'),

    // Catálogo de tokens con mapeo conocido por tipo de documento (nombres
    // canónicos, sin delimitadores ni espacios). Fase 6 = CARTA, Fase 7 = RESOLUCION.
    'tokens_conocidos' => [
        'CARTA' => [
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
        ],
        'RESOLUCION' => [
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
        ],
    ],

];
