<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocenteRequest;
use App\Models\Docente;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class DocenteController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Docente::class, 'docente');
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $docentes = Docente::query()
            ->with(['escuela.facultad'])
            ->when($q !== '', function ($query) use ($q) {
                // Búsqueda (autocomplete del registro): solo docentes activos, límite 20.
                $like = '%'.addcslashes($q, '%_').'%';
                $query->where('activo', true)
                    ->where(function ($w) use ($like) {
                        $w->where('dni', 'like', $like)
                            ->orWhere('nombres', 'like', $like)
                            ->orWhere('apellido_paterno', 'like', $like)
                            ->orWhere('apellido_materno', 'like', $like);
                    })
                    ->limit(20);
            })
            ->orderByDesc('activo')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombres')
            ->get();

        return response()->json($docentes);
    }

    public function store(DocenteRequest $request)
    {
        $docente = Docente::create($request->validated());

        return response()->json($docente->load('escuela.facultad'), 201);
    }

    public function show(Docente $docente)
    {
        return response()->json($docente->load('escuela.facultad'));
    }

    public function update(DocenteRequest $request, Docente $docente)
    {
        $docente->update($request->validated());

        return response()->json($docente->refresh()->load('escuela.facultad'));
    }

    public function destroy(Docente $docente)
    {
        // Baja lógica: se desactiva el docente y se registra deleted_at (soft delete).
        $docente->forceFill(['activo' => false])->save();
        $docente->delete();

        return response()->json(null, 204);
    }
}
