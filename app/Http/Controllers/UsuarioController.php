<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\ActorHistorico;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class UsuarioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $this->esAdmin($request)) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $filtros = $request->validate([
            'busqueda' => ['sometimes', 'nullable', 'string', 'max:200'],
            'nombre' => ['sometimes', 'nullable', 'string', 'max:200'],
            'email' => ['sometimes', 'nullable', 'string', 'max:200'],
            'activo' => ['sometimes', 'nullable', 'boolean'],
            'desde' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'hasta' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ]);

        $usuarios = Usuario::with('rolRef')
            ->whereNull('deleted_at')
            ->when(
                ! empty($filtros['busqueda']),
                function ($query) use ($filtros) {
                    foreach (preg_split('/\s+/', trim($filtros['busqueda'])) as $termino) {
                        $busqueda = '%'.$termino.'%';
                        $query->where(fn ($sub) => $sub->whereLike('nombres', $busqueda)
                            ->orWhereLike('apellidos', $busqueda)
                            ->orWhereLike('email', $busqueda)
                            ->orWhereLike('dni', $busqueda));
                    }
                }
            )
            ->when(
                ! empty($filtros['nombre']),
                fn ($query) => $query->where(function ($sub) use ($filtros) {
                    $nombre = '%'.$filtros['nombre'].'%';
                    $sub->whereLike('nombres', $nombre)->orWhereLike('apellidos', $nombre);
                })
            )
            ->when(
                ! empty($filtros['email']),
                fn ($query) => $query->whereLike('email', '%'.$filtros['email'].'%')
            )
            ->when(
                isset($filtros['activo']),
                fn ($query) => $query->where('activo', $request->boolean('activo'))
            )
            ->when(isset($filtros['desde']), fn ($query) => $query->whereDate('created_at', '>=', $filtros['desde']))
            ->when(isset($filtros['hasta']), fn ($query) => $query->whereDate('created_at', '<=', $filtros['hasta']))
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($usuarios);
    }

    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        if (! $this->esAdmin($request)) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $data = $request->validated();

        $password = sprintf('%06d', random_int(0, 999999));

        try {
            $usuario = DB::transaction(function () use ($data, $password): Usuario {
                $atributos = [
                    'dni' => $data['dni'],
                    'nombres' => $data['nombres'],
                    'apellidos' => $data['apellidos'],
                    'email' => $data['email'],
                    'rol_id' => $data['rol_id'],
                    'password_hash' => Hash::make($password),
                    'activo' => $data['activo'] ?? true,
                ];

                $usuario = Usuario::withTrashed()->where('dni', $data['dni'])->lockForUpdate()->first();
                if ($usuario && ! $usuario->trashed()) {
                    throw ValidationException::withMessages(['dni' => 'Ya existe un usuario registrado con ese DNI.']);
                }

                if ($usuario) {
                    $usuario->fill($atributos);
                    $usuario->google_sub = null;
                    $usuario->deleted_at = null;
                    $usuario->save();
                } else {
                    $usuario = Usuario::create($atributos);
                }

                $this->guardarActorHistorico($usuario);

                Mail::send(
                    'emails.usuario-registrado',
                    [
                        'nombre' => $usuario->nombres,
                        'correo' => $usuario->email,
                        'password' => $password,
                        'loginUrl' => rtrim(config('app.frontend_url'), '/').'/login',
                    ],
                    fn ($message) => $message->to($usuario->email)->subject('Acceso al SGR VRIN')
                );

                return $usuario;
            });
        } catch (TransportExceptionInterface $e) {
            return response()->json([
                'message' => 'No se pudo enviar la contraseña por correo. Revisa las credenciales SMTP; el usuario no fue registrado.',
            ], 503);
        }

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

        DB::transaction(function () use ($usuario, $data): void {
            $usuario->update($data);
            $this->guardarActorHistorico($usuario);
        });

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

        try {
            DB::transaction(function () use ($usuario): void {
                $this->guardarActorHistorico($usuario, true);
                $usuario->forceDelete();
            });
        } catch (QueryException $e) {
            if (in_array($e->errorInfo[1] ?? null, [1451, 19], true) || $e->getCode() === '23503') {
                return response()->json([
                    'message' => 'No se pudo eliminar la cuenta porque existe una referencia que aún no está archivada. Contacta al administrador del sistema.',
                ], 409);
            }

            throw $e;
        }

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

    public function consultarDni(Request $request): JsonResponse
    {
        if (! $this->esAdmin($request)) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $datos = $request->validate(['dni' => ['required', 'digits:8']]);
        $dni = $datos['dni'];

        if (Usuario::where('dni', $dni)->exists()) {
            return response()->json(['registrado' => true, 'message' => 'Usuario ya registrado.']);
        }

        $token = config('services.apisperu.token');
        if (! $token) {
            return response()->json(['message' => 'La consulta de DNI no está configurada.'], 503);
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
            return response()->json(['message' => 'El servicio de DNI no devolvió nombres completos.'], 502);
        }

        return response()->json([
            'registrado' => false,
            'datos' => [
                'nombres' => trim($nombres),
                'apellidos' => trim($apellidoPaterno.' '.(string) $respuesta->json('apellidoMaterno', '')),
            ],
        ]);
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

    private function guardarActorHistorico(Usuario $usuario, bool $archivar = false): void
    {
        $usuario->load('rolRef');

        ActorHistorico::updateOrCreate(
            ['id' => $usuario->id],
            [
                'nombres' => $usuario->nombres,
                'apellidos' => $usuario->apellidos,
                'rol_nombre' => $usuario->rolRef?->nombre ?? 'Sin rol',
                'fecha_alta' => $usuario->created_at,
                'archivado_at' => $archivar ? now() : null,
            ]
        );
    }
}
