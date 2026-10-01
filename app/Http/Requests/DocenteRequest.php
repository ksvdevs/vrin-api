<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización la maneja DocentePolicy vía authorizeResource en el controlador.
        return true;
    }

    public function rules(): array
    {
        return [
            'dni' => [
                'required',
                'regex:/^[0-9]{8}$/',
                Rule::unique('docentes', 'dni')->ignore($this->route('docente')),
            ],
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:80'],
            'apellido_materno' => ['nullable', 'string', 'max:80'],
            'grado' => [
                'required',
                Rule::in(['Dr.', 'Dra.', 'Mg.', 'M.Sc.', 'Ph.D.', 'CPC', 'Ing.', 'Lic.', 'Abog.', 'Otro']),
            ],
            'tipo_contrato' => ['required', Rule::in(['NOMBRADO', 'CONTRATADO'])],
            'escuela_id' => ['required', 'integer', Rule::exists('escuelas', 'id')],
            'email' => ['nullable', 'email', 'max:190'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.regex' => 'El DNI debe tener exactamente 8 dígitos numéricos.',
            'dni.unique' => 'Ya existe un docente registrado con ese DNI.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
            'grado.required' => 'El grado académico es obligatorio.',
            'grado.in' => 'El grado académico seleccionado no es válido.',
            'tipo_contrato.required' => 'El tipo de contrato es obligatorio.',
            'tipo_contrato.in' => 'El tipo de contrato debe ser NOMBRADO o CONTRATADO.',
            'escuela_id.required' => 'La escuela es obligatoria.',
            'escuela_id.exists' => 'La escuela seleccionada no existe.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
        ];
    }
}
