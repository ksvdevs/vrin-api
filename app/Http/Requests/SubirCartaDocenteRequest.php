<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubirCartaDocenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización la maneja ExpedientePolicy en el controlador.
        return true;
    }

    public function rules(): array
    {
        return [
            // Se valida por extensión del cliente (no por MIME real) para aceptar
            // escaneos con contenido no detectable; el tamaño replica ck_arch_tamano (25 MB).
            'archivo' => ['required', 'file', 'extensions:pdf,doc,docx,jpg,jpeg,png', 'max:25600'],
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.required' => 'Debe adjuntar el archivo de la carta del docente.',
            'archivo.file' => 'El archivo adjunto no es válido.',
            'archivo.extensions' => 'El archivo debe ser PDF, DOC, DOCX, JPG o PNG.',
            'archivo.max' => 'El archivo no puede superar los 25 MB.',
        ];
    }
}
