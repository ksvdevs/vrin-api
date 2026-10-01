<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->rol !== 'ADMINISTRADOR') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json(Usuario::orderBy('nombre')->get());
    }

    public function store(Request $request)
    {
        if ($request->user()->rol !== 'ADMINISTRADOR') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|max:150|ends_with:@unamba.edu.pe|unique:usuarios,email',
            'rol' => 'required|in:ADMINISTRADOR,SECRETARIA,CALIDAD',
        ]);

        $usuario = Usuario::create(array_merge($data, ['activo' => true]));

        return response()->json($usuario, 201);
    }

    public function update($id, Request $request)
    {
        if ($request->user()->rol !== 'ADMINISTRADOR') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $usuario = Usuario::findOrFail($id);

        $data = $request->validate([
            'activo' => 'required|boolean',
        ]);

        $usuario->update(['activo' => $data['activo']]);

        return response()->json($usuario);
    }
}
