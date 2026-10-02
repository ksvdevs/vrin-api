<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $this->esAdmin($request)) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $usuarios = Usuario::with('rolRef')
            ->whereNull('deleted_at')
            ->when(
                $request->filled('nombre'),
                fn ($query) => $query->where(function ($sub) use ($request) {
                    $nombre = '%'.$request->input('nombre').'%';
                    $sub->whereLike('nombres', $nombre)->orWhereLike('apellidos', $nombre);
                })
            )
            ->when(
                $request->filled('email'),
                fn ($query) => $query->whereLike('email', '%'.$request->input('email').'%')
            )
            ->when(
                $request->has('activo') && $request->input('activo') !== null,
                fn ($query) => $query->where('activo', $request->boolean('activo'))
            )
            ->orderByDesc('created_at')
            ->get();

        return response()->json($usuarios);
    }

    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        if (! $this->esAdmin($request)) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $data = $request->validated();

        $usuario = Usuario::create([
            'dni' => $data['dni'],
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'email' => $data['email'],
            'rol_id' => $data['rol_id'],
            'password_hash' => Hash::make($data['password']),
            'activo' => $data['activo'] ?? true,
        ]);

        return response()->json($usuario->load('rolRef'), 201);
    }

    public function show(Request $request, Usuario $usuario): JsonResponse
    {
        if (! $this->esAdmin($request)) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return response()->json($usuario->load('rolRef'));
    }

    public function update(UpdateUsuarioRequest $request, Usuario $usuario): JsonResponse
    {
        if (! $this->esAdmin($request)) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password_hash'] = Hash::make($data['password']);
        }
        unset($data['password']);

        $usuario->update($data);

        return response()->json($usuario->load('rolRef'));
    }

    public function destroy(Request $request, Usuario $usuario): JsonResponse
    {
        if (! $this->esAdmin($request)) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        if ($usuario->id === $request->user()->id) {
            return response()->json(['message' => 'No puedes eliminar tu propio usuario.'], 422);
        }

        $usuario->deleted_at = now();
        $usuario->save();

        return response()->json(['message' => 'Usuario eliminado.']);
    }

    public function validarDni(Request $request): JsonResponse
    {
        $request->validate([
            'dni' => ['required', 'digits:8'],
        ]);

        $existe = Usuario::where('dni', $request->input('dni'))
            ->whereNull('deleted_at')
            ->exists();

        return response()->json(['disponible' => ! $existe]);
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
