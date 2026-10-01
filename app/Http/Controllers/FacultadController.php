<?php

namespace App\Http\Controllers;

use App\Models\Facultad;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FacultadController extends Controller
{
    public function index()
    {
        $facultades = Facultad::query()
            ->where('activo', true)
            ->with(['escuelas' => fn ($q) => $q
                ->where('activo', true)
                ->orderBy('nombre')
                ->select(['id', 'facultad_id', 'nombre']),
            ])
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($facultades->map(fn ($f) => [
            'id' => $f->id,
            'nombre' => $f->nombre,
            'escuelas' => $f->escuelas
                ->map(fn ($e) => ['id' => $e->id, 'nombre' => $e->nombre])
                ->values(),
        ])->values());
    }

    public function store(Request $request)
    {
        abort_unless(
            $request->user()?->rol === 'ADMINISTRADOR',
            403,
            'Solo el rol ADMINISTRADOR puede registrar facultades.'
        );

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150', Rule::unique('facultades', 'nombre')],
        ], [
            'nombre.required' => 'El nombre de la facultad es obligatorio.',
            'nombre.unique' => 'Ya existe una facultad con ese nombre.',
        ]);

        $facultad = Facultad::create($data);

        return response()->json($facultad->only('id', 'nombre'), 201);
    }
}
