<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RolController extends Controller
{
    /**
     * Roles semilla del sistema: no se pueden eliminar.
     */
    private const ROLES_SISTEMA = ['Administrador General', 'Secretaría', 'Calidad'];

    public function index(Request $request): JsonResponse
    {
        if (! $this->esAdmin($request)) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $roles = Rol::query()
            ->when(
                $request->filled('nombre'),
                fn ($query) => $query->whereLike('nombre', '%'.$request->input('nombre').'%')
            )
            ->when(
                $request->has('activo') && $request->input('activo') !== null,
                fn ($query) => $query->where('activo', $request->boolean('activo'))
            )
            ->orderByDesc('created_at')
            ->get();

        return response()->json($roles);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->esAdmin($request)) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:60', 'unique:roles,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activo' => ['boolean'],
        ]);

        $rol = Rol::create([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'activo' => $data['activo'] ?? true,
        ]);

        return response()->json($rol, 201);
    }

    public function update(Request $request, Rol $rol): JsonResponse
    {
        if (! $this->esAdmin($request)) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:60', Rule::unique('roles', 'nombre')->ignore($rol->id)],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activo' => ['boolean'],
        ]);

        $rol->update([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'activo' => $data['activo'] ?? $rol->activo,
        ]);

        return response()->json($rol);
    }

    public function destroy(Request $request, Rol $rol): JsonResponse
    {
        if (! $this->esAdmin($request)) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        // Cuenta también los eliminados lógicos: la FK fk_usuarios_rol es
        // RESTRICT y bloquea el DELETE mientras exista cualquier fila hija.
        $usuariosConRol = Usuario::where('rol_id', $rol->id)->count();

        if ($usuariosConRol > 0) {
            return response()->json([
                'message' => "No se puede eliminar: hay {$usuariosConRol} usuario(s) con este rol.",
            ], 422);
        }

        if (in_array($rol->nombre, self::ROLES_SISTEMA, true)) {
            return response()->json(['message' => 'Rol del sistema: no se puede eliminar.'], 422);
        }

        $rol->delete();

        return response()->json(['message' => 'Rol eliminado.']);
    }

    private function esAdmin(Request $request): bool
    {
        $usuario = $request->user();

        if ($usuario === null) {
            return false;
        }

        $usuario->loadMissing('rolRef');

        return $usuario->rol_codigo === 'ADMINISTRADOR_GENERAL'
            || Str::contains(Str::lower($usuario->rol ?? ''), 'administrador');
    }
}
