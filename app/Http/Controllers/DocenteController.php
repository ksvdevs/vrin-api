<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocenteRequest;
use App\Models\Docente;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
            'genero' => ['sometimes', 'nullable', 'string', 'max:20'],
            'escuela_id' => ['sometimes', 'nullable', 'integer'],
        ]);

        $docentes = Docente::query()
            ->with(['escuela.facultad'])
            ->when(! empty($filtros['busqueda']), function ($query) use ($filtros) {
                foreach (preg_split('/\s+/u', trim($filtros['busqueda']), -1, PREG_SPLIT_NO_EMPTY) as $termino) {
                    $like = '%'.addcslashes($termino, '%_').'%';
                    $query->where(fn ($w) => $w->where('nombres', 'like', $like)
                        ->orWhere('apellido_paterno', 'like', $like)
                        ->orWhere('apellido_materno', 'like', $like)
                        ->orWhere('dni', 'like', $like)
                        ->orWhereHas('escuela', fn ($escuela) => $escuela->where('nombre', 'like', $like)));
                }
            })
            ->when(isset($filtros['activo']), fn ($query) => $query->where('activo', $request->boolean('activo')))
            ->when($filtros['categoria'] ?? null, fn ($query, $categoria) => $query->where('categoria', $categoria))
            ->when($filtros['genero'] ?? null, fn ($query, $genero) => $query->where('genero', $genero))
            ->when($filtros['escuela_id'] ?? null, fn ($query, $escuelaId) => $query->where('escuela_id', $escuelaId))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json($docentes);
    }

    public function consultarDni(Request $request)
    {
        $this->authorize('create', Docente::class);
        $datos = $request->validate(['dni' => ['required', 'digits:8']]);
        $dni = $datos['dni'];

        if (Docente::where('dni', $dni)->exists()) {
            return response()->json(['registrado' => true, 'message' => 'Docente ya registrado']);
        }

        $token = config('services.apisperu.token');
        if (! $token) {
            return response()->json(['message' => 'La consulta de DNI no esta configurada.'], 503);
        }

        try {
            $respuesta = Http::timeout(8)->get('https://dniruc.apisperu.com/api/v1/dni/'.$dni, [
                'token' => $token,
            ]);
        } catch (ConnectionException $e) {
            return response()->json(['message' => 'No se pudo conectar al servicio de DNI.'], 503);
        }

        if (! $respuesta->successful() || ! $respuesta->json('success', true)) {
            return response()->json(['message' => 'No se pudieron obtener los datos de este DNI.'], 502);
        }

        $nombres = $respuesta->json('nombres');
        $apellidoPaterno = $respuesta->json('apellidoPaterno');
        if (! is_string($nombres) || trim($nombres) === '' || ! is_string($apellidoPaterno) || trim($apellidoPaterno) === '') {
            return response()->json(['message' => 'El servicio de DNI no devolvio nombres completos.'], 502);
        }

        return response()->json([
            'registrado' => false,
            'datos' => [
                'nombres' => trim($nombres),
                'apellido_paterno' => trim($apellidoPaterno),
                'apellido_materno' => trim((string) $respuesta->json('apellidoMaterno', '')),
            ],
        ]);
    }

    public function store(DocenteRequest $request)
    {
        $datos = $request->validated();
        $datos['tipo_contrato'] = $datos['categoria'] === 'CONTRATADO' ? 'CONTRATADO' : 'NOMBRADO';
        $docente = DB::transaction(function () use ($datos) {
            $eliminado = Docente::onlyTrashed()->where('dni', $datos['dni'])->lockForUpdate()->first();
            if ($eliminado) {
                $eliminado->restore();
                $eliminado->fill($datos);
                $eliminado->created_at = now();
                $eliminado->save();

                return $eliminado;
            }

            return Docente::create($datos);
        });

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
        if ($docente->expedientes()->withTrashed()->exists()) {
            return response()->json([
                'message' => 'El docente tiene expedientes registrados. Desactívalo desde Modificar docente.',
            ], 409);
        }

        $docente->forceDelete();

        return response()->json(null, 204);
    }
}
