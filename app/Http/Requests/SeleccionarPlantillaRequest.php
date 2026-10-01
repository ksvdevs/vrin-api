<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SeleccionarPlantillaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización la maneja PlantillaPolicy::seleccionar en el controlador.
        return true;
    }

    public function rules(): array
    {
        return [
            'modulo' => ['required', 'string', 'max:20'],
            'tipo_documento_id' => ['required', 'integer', Rule::exists('tipos_documento_plantilla', 'id')],
            'plantilla_id' => ['required', 'integer', Rule::exists('plantillas', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'modulo.required' => 'El módulo es obligatorio.',
            'tipo_documento_id.required' => 'El tipo de documento es obligatorio.',
            'tipo_documento_id.exists' => 'El tipo de documento seleccionado no existe.',
            'plantilla_id.required' => 'La plantilla es obligatoria.',
            'plantilla_id.exists' => 'La plantilla seleccionada no existe.',
        ];
    }
}
