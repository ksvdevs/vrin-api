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

    // Ruta del binario de LibreOffice para la conversión DOCX→PDF (D-23).
    // SOFFICE_PATH en .env gana; si no, se detecta la instalación habitual.
    'soffice_path' => env('SOFFICE_PATH') ?? collect([
        'C:\Program Files\LibreOffice\program\soffice.exe',
        'C:\Program Files (x86)\LibreOffice\program\soffice.exe',
        '/usr/bin/soffice',
        '/opt/libreoffice/program/soffice',
    ])->first(fn (string $ruta) => file_exists($ruta)) ?? 'soffice',

];
