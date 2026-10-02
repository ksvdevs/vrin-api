<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización (solo administrador) la valida UsuarioController.
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $usuario = $this->route('usuario');

        return [
            'dni' => ['sometimes', 'digits:8', Rule::unique('usuarios', 'dni')->ignore($usuario)],
            'nombres' => ['sometimes', 'string', 'max:100'],
            'apellidos' => ['sometimes', 'string', 'max:150'],
            'email' => ['sometimes', 'email', 'ends_with:@unamba.edu.pe', Rule::unique('usuarios', 'email')->ignore($usuario)],
            'password' => ['nullable', 'string', 'min:8'],
            'rol_id' => ['sometimes', 'exists:roles,id'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos numéricos.',
            'dni.unique' => 'Ya existe un usuario registrado con ese DNI.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'email.ends_with' => 'El correo debe pertenecer al dominio @unamba.edu.pe.',
            'email.unique' => 'Ya existe un usuario registrado con ese correo.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'rol_id.exists' => 'El rol seleccionado no existe.',
        ];
    }
}
