<?php

namespace App\Http\Controllers;

use App\Models\Escuela;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EscuelaController extends Controller
{
    public function store(Request $request)
    {
        abort_unless(
            $request->user()?->rol === 'ADMINISTRADOR',
            403,
            'Solo el rol ADMINISTRADOR puede registrar escuelas.'
        );

        $data = $request->validate([
            'facultad_id' => ['required', 'integer', Rule::exists('facultades', 'id')],
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('escuelas', 'nombre')->where('facultad_id', $request->input('facultad_id')),
            ],
        ], [
            'facultad_id.required' => 'La facultad es obligatoria.',
            'facultad_id.exists' => 'La facultad seleccionada no existe.',
            'nombre.required' => 'El nombre de la escuela es obligatorio.',
            'nombre.unique' => 'Ya existe una escuela con ese nombre en la facultad seleccionada.',
        ]);

        $escuela = Escuela::create($data);

        return response()->json($escuela->only('id', 'facultad_id', 'nombre'), 201);
    }
}
