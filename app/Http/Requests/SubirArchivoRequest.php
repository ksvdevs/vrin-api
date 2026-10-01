<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Subida genérica de archivos del expediente (RN-03): tipo y etapa opcionales
 * con valores por defecto; el flujo de registro de Etapa 1 sigue enviando
 * solo el campo «archivo» (CARTA_DOCENTE, etapa 1).
 */
class SubirArchivoRequest extends FormRequest
{
    public const TIPOS = 'CARTA_DOCENTE,CARTA_OPP,ANEXO,COMPROBANTE_RENDICION';

    /** Etapa por defecto de cada tipo de archivo. */
    public const ETAPA_POR_TIPO = [
        'CARTA_DOCENTE' => 1,
        'CARTA_OPP' => 2,
        'ANEXO' => 3,
        'COMPROBANTE_RENDICION' => 4,
    ];

    public function authorize(): bool
    {
        // La autorización la maneja ExpedientePolicy en el controlador.
        return true;
    }

    public function rules(): array
    {
        return [
            'archivo' => ['required', 'file', 'extensions:pdf,doc,docx,jpg,jpeg,png', 'max:25600'],
            'tipo' => ['sometimes', 'in:'.self::TIPOS],
            'etapa' => ['sometimes', 'integer', 'between:1,4'],
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.required' => 'Debe adjuntar el archivo.',
            'archivo.extensions' => 'El archivo debe ser PDF, DOC, DOCX, JPG o PNG.',
            'archivo.max' => 'El archivo no puede superar los 25 MB.',
            'tipo.in' => 'El tipo de archivo no es válido.',
            'etapa.between' => 'La etapa debe estar entre 1 y 4.',
        ];
    }
}
