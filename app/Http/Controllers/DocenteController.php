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
        $soloNombre = $request->boolean('solo_nombre');

        // Autocompletado (registro de expediente): array plano de activos, máx. 20.
        if ($q !== '') {
            $terminos = preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY);

            $docentes = Docente::query()
                ->with(['escuela.facultad'])
                ->where('activo', true)
                ->where(function ($query) use ($terminos, $soloNombre) {
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
                })
                ->orderBy('apellido_paterno')
                ->orderBy('apellido_materno')
                ->orderBy('nombres')
                ->limit(20)
                ->get();

            return response()->json($docentes);
        }

        $filtros = $request->validate([
            'busqueda' => ['sometimes', 'nullable', 'string', 'max:200'],
            'activo' => ['sometimes', 'nullable', 'boolean'],
            'categoria' => ['sometimes', 'nullable', 'string', 'max:30'],
            'grado' => ['sometimes', 'nullable', 'string', 'max:30'],
        ]);

        $docentes = Docente::query()
            ->with(['escuela.facultad'])
            ->when(! empty($filtros['busqueda']), function ($query) use ($filtros) {
                foreach (preg_split('/\s+/u', trim($filtros['busqueda']), -1, PREG_SPLIT_NO_EMPTY) as $termino) {
                    $like = '%'.addcslashes($termino, '%_').'%';
                    $query->where(fn ($w) => $w->where('nombres', 'like', $like)
                        ->orWhere('apellido_paterno', 'like', $like)
                        ->orWhere('apellido_materno', 'like', $like)
                        ->orWhere('dni', 'like', $like));
                }
            })
            ->when(isset($filtros['activo']), fn ($query) => $query->where('activo', $request->boolean('activo')))
            ->when($filtros['categoria'] ?? null, fn ($query, $categoria) => $query->where('categoria', $categoria))
            ->when($filtros['grado'] ?? null, fn ($query, $grado) => $query->where('grado', $grado))
            ->orderByDesc('activo')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombres')
            ->paginate(15);

        return response()->json($docentes);
    }

    public function store(DocenteRequest $request)
    {
        $datos = $request->validated();
        $datos['tipo_contrato'] = $datos['categoria'] === 'CONTRATADO' ? 'CONTRATADO' : 'NOMBRADO';
        $docente = Docente::create($datos);

        return response()->json($docente->load('escuela.facultad'), 201);
    }

    public function show(Docente $docente)
    {
        return response()->json($docente->load('escuela.facultad'));
    }

    public function update(DocenteRequest $request, Docente $docente)
    {
        $datos = $request->validated();
        if (isset($datos['categoria'])) {
            $datos['tipo_contrato'] = $datos['categoria'] === 'CONTRATADO' ? 'CONTRATADO' : 'NOMBRADO';
        }
        $docente->update($datos);

        return response()->json($docente->refresh()->load('escuela.facultad'));
    }

    public function cambiarEstado(Request $request, Docente $docente)
    {
        $this->authorize('update', $docente);

        $datos = $request->validate(['activo' => ['required', 'boolean']]);
        $docente->update($datos);

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
