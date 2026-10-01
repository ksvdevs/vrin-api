<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidarExpedienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización la maneja ExpedientePolicy::validarRequisitos en el controlador.
        return true;
    }

    public function rules(): array
    {
        return [
            'resultado' => ['required', Rule::in(['CUMPLE', 'NO_CUMPLE'])],
            'checklist' => ['required', 'array'],
            'checklist.carta_aceptacion' => ['required', 'boolean'],
            'checklist.docente_ordinario_contratado' => ['required', 'boolean'],
            'checklist.afiliacion_universidad' => ['required', 'boolean'],
            'observacion' => ['required_if:resultado,NO_CUMPLE', 'nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'resultado.required' => 'El resultado de la validación es obligatorio.',
            'resultado.in' => 'El resultado debe ser CUMPLE o NO_CUMPLE.',
            'checklist.required' => 'El checklist de requisitos es obligatorio.',
            'checklist.carta_aceptacion.required' => 'Debe marcar si la carta oficial de aceptación cumple.',
            'checklist.docente_ordinario_contratado.required' => 'Debe marcar si el docente ordinario o contratado cumple.',
            'checklist.afiliacion_universidad.required' => 'Debe marcar si la afiliación a la universidad cumple.',
            'observacion.required_if' => 'Debe registrar una observación cuando el resultado es NO_CUMPLE.',
        ];
    }
}
