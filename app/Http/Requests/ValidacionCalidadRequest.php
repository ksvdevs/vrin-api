<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidacionCalidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización la maneja ExpedientePolicy en el controlador.
        return true;
    }

    public function rules(): array
    {
        return [
            'resultado' => ['required', 'in:CUMPLE,NO_CUMPLE'],
            'checklist' => ['required', 'array'],
            'checklist.carta_aceptacion' => ['required', 'boolean'],
            'checklist.docente_ordinario_contratado' => ['required', 'boolean'],
            'checklist.afiliacion_universidad' => ['required', 'boolean'],
            'observacion' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'resultado.required' => 'El resultado de la validación es obligatorio.',
            'resultado.in' => 'El resultado debe ser CUMPLE o NO_CUMPLE.',
            'checklist.required' => 'El checklist de requisitos es obligatorio.',
            'checklist.array' => 'El checklist de requisitos no es válido.',
            'checklist.carta_aceptacion.required' => 'Debe indicar la Carta Oficial de Aceptación.',
            'checklist.carta_aceptacion.boolean' => 'El valor de la Carta Oficial de Aceptación no es válido.',
            'checklist.docente_ordinario_contratado.required' => 'Debe indicar Docente Ordinario o Contratado.',
            'checklist.docente_ordinario_contratado.boolean' => 'El valor de Docente Ordinario/Contratado no es válido.',
            'checklist.afiliacion_universidad.required' => 'Debe indicar la Afiliación a la Universidad.',
            'checklist.afiliacion_universidad.boolean' => 'El valor de Afiliación a la Universidad no es válido.',
        ];
    }
}
