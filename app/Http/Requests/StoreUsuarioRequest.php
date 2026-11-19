<?php

namespace App\Http\Requests;

use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUsuarioRequest extends FormRequest
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
        $usuarioEliminado = Usuario::withTrashed()
            ->where('dni', $this->input('dni'))
            ->whereNotNull('deleted_at')
            ->first();

        return [
            'dni' => ['required', 'digits:8', Rule::unique('usuarios', 'dni')->ignore($usuarioEliminado?->id)],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'ends_with:@unamba.edu.pe', Rule::unique('usuarios', 'email')->ignore($usuarioEliminado?->id)],
            'password' => ['prohibited'],
            'rol_id' => ['required', 'exists:roles,id'],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos numéricos.',
            'dni.unique' => 'Ya existe un usuario registrado con ese DNI.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'email.ends_with' => 'El correo debe pertenecer al dominio @unamba.edu.pe.',
            'email.unique' => 'Ya existe un usuario registrado con ese correo.',
            'password.prohibited' => 'La contraseña se genera y envía al correo automáticamente.',
            'rol_id.required' => 'El rol es obligatorio.',
            'rol_id.exists' => 'El rol seleccionado no existe.',
        ];
    }
}
