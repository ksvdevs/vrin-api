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
            'version_actual' => [$this->isMethod('PUT') ? 'required' : 'sometimes', 'integer', 'min:1'],
            'numero' => ['required', 'integer', 'min:1'],
            'anio' => ['required', 'integer', 'between:2020,2100'],
            'fecha' => ['required', 'date'],
            'ciudad' => ['nullable', 'string', 'max:60'],
            'registro_mp_numero' => ['nullable', 'string', 'max:30'],
            'carta_docente_registro_numero' => ['nullable', 'string', 'max:80'],
            'carta_docente_registro_fecha' => ['nullable', 'date'],
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
            'carta_docente_registro_numero.max' => 'El registro de la carta docente no puede superar los 80 caracteres.',
            'carta_docente_registro_fecha.date' => 'La fecha de registro de la carta docente no es válida.',
        ];
    }
}
