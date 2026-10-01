<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubirPlantillaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización la maneja PlantillaPolicy vía authorize() en el controlador.
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150'],
            'tipo_documento_id' => ['required', 'integer', Rule::exists('tipos_documento_plantilla', 'id')],
            'modulo' => ['sometimes', 'string', 'max:20'],
            'archivo' => [
                'required',
                'file',
                'mimes:docx',
                'mimetypes:application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'max:25600', // 25 MB (RF-42)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la plantilla es obligatorio.',
            'tipo_documento_id.required' => 'El tipo de documento es obligatorio.',
            'tipo_documento_id.exists' => 'El tipo de documento seleccionado no existe.',
            'archivo.required' => 'Debe adjuntar el archivo DOCX de la plantilla.',
            'archivo.mimes' => 'La plantilla debe ser un archivo .docx.',
            'archivo.mimetypes' => 'La plantilla debe ser un documento Word (.docx) válido.',
            'archivo.max' => 'La plantilla no puede superar los 25 MB.',
        ];
    }
}
