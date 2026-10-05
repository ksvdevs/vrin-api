<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerarCartaVrinRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización la maneja ExpedientePolicy en el controlador.
        return true;
    }

    public function rules(): array
    {
        return [
            'numero' => ['required', 'integer', 'min:1'],
            'anio' => ['required', 'integer', 'between:2020,2100'],
            'fecha' => ['required', 'date'],
            'ciudad' => ['nullable', 'string', 'max:60'],
            'registro_mp_numero' => ['nullable', 'string', 'max:30'],
            'asunto' => ['nullable', 'string', 'max:255'],
            'fecha_aceptacion' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero.required' => 'El número de carta es obligatorio.',
            'numero.integer' => 'El número de carta debe ser entero.',
            'numero.min' => 'El número de carta debe ser mayor que cero.',
            'anio.required' => 'El año de la carta es obligatorio.',
            'anio.between' => 'El año de la carta no es válido.',
            'fecha.required' => 'La fecha de emisión de la carta es obligatoria.',
            'fecha.date' => 'La fecha de emisión no es válida.',
            'registro_mp_numero.max' => 'El registro de mesa de partes no puede superar los 30 caracteres.',
        ];
    }
}
