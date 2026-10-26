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
            'genero' => [$this->isMethod('post') ? 'required' : 'nullable', Rule::in(['MASCULINO', 'FEMENINO', 'OTRO'])],
            'celular' => ['nullable', 'regex:/^9[0-9]{8}$/'],
            'grado' => [
                'required',
                Rule::in(['Dr.', 'Dra.', 'Mg.', 'M.Sc.', 'Ph.D.', 'CPC', 'Ing.', 'Lic.', 'Abog.', 'Otro']),
            ],
            'categoria' => [$this->isMethod('post') ? 'required' : 'nullable', Rule::in(['ORDINARIO_PRINCIPAL', 'ORDINARIO_ASOCIADO', 'ORDINARIO_AUXILIAR', 'CONTRATADO'])],
            'escuela_id' => ['required', 'integer', Rule::exists('escuelas', 'id')],
            'email' => [$this->isMethod('post') ? 'required' : 'nullable', 'email', 'max:190'],
            'activo' => [$this->isMethod('post') ? 'required' : 'sometimes', 'boolean'],
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
            'genero.required' => 'El género es obligatorio.',
            'genero.in' => 'El género seleccionado no es válido.',
            'celular.regex' => 'El celular debe tener 9 dígitos y empezar con 9.',
            'grado.required' => 'El grado académico es obligatorio.',
            'grado.in' => 'El grado académico seleccionado no es válido.',
            'categoria.required' => 'La categoría es obligatoria.',
            'categoria.in' => 'La categoría seleccionada no es válida.',
            'escuela_id.required' => 'La escuela es obligatoria.',
            'escuela_id.exists' => 'La escuela seleccionada no existe.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'email.required' => 'El email es obligatorio.',
            'activo.required' => 'El estado es obligatorio.',
        ];
    }
}
