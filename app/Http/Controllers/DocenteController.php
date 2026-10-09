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
        $terminos = preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY);
        $soloNombre = $request->boolean('solo_nombre');

        $docentes = Docente::query()
            ->with(['escuela.facultad'])
            ->when($q !== '', function ($query) use ($terminos, $soloNombre) {
                $query->where('activo', true);
                foreach ($terminos as $termino) {
                    $like = '%'.addcslashes($termino, '%_').'%';
                    $query->where(function ($w) use ($like, $soloNombre) {
                        $w->where('nombres', 'like', $like)
                            ->orWhere('apellido_paterno', 'like', $like)
                            ->orWhere('apellido_materno', 'like', $like);
                        if (! $soloNombre) {
                            $w->orWhere('dni', 'like', $like);
                        }
                    });
                }
                $query->limit(20);
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
