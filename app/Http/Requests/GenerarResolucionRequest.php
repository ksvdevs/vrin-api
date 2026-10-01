<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerarResolucionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero' => ['required', 'integer', 'min:1'],
            'anio' => ['required', 'integer', 'between:2020,2100'],
            'fecha_emision' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero.required' => 'El número de resolución es obligatorio.',
            'numero.integer' => 'El número de resolución debe ser entero.',
            'numero.min' => 'El número de resolución debe ser mayor que cero.',
            'anio.required' => 'El año de la resolución es obligatorio.',
            'anio.between' => 'El año de la resolución no es válido.',
            'fecha_emision.required' => 'La fecha de emisión es obligatoria.',
            'fecha_emision.date' => 'La fecha de emisión no es válida.',
        ];
    }
}
