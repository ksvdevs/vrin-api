<?php

use App\Http\Controllers\DocenteController;
use App\Http\Controllers\EscuelaController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\FacultadController;
use App\Http\Controllers\PlantillaController;
use App\Http\Controllers\PlantillaSeleccionController;
use App\Http\Controllers\ValidacionController;
use App\Http\Middleware\DevRoleAuthMiddleware;
use App\Models\Facultad;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(DevRoleAuthMiddleware::class)->group(function () {

    // Fase 1: catálogos en cascada (facultades con escuelas activas).
    Route::get('/facultades', [FacultadController::class, 'index']);
    Route::post('/facultades', [FacultadController::class, 'store']);
    Route::post('/escuelas', [EscuelaController::class, 'store']);

    // Fase 1: búsqueda (?q=) y CRUD de docentes (destroy = baja lógica).
    Route::apiResource('docentes', DocenteController::class);

    // Fase 2 — Etapa 1: registro de expediente (sin OCR) y carta del docente.
    Route::post('/expedientes', [ExpedienteController::class, 'store']);
    Route::post('/expedientes/{expediente}/archivos', [ExpedienteController::class, 'subirArchivo']);

    // Fase 3 — Bandeja de expedientes y vista detalle.
    Route::get('/expedientes', [ExpedienteController::class, 'index']);
    Route::get('/expedientes/{expediente}', [ExpedienteController::class, 'show']);
    Route::get('/expedientes/{expediente}/archivos/{archivo}', [ExpedienteController::class, 'archivo']);

    // Fase 4 — Validación de Calidad (RN-01/RN-02) y subsanación (RN-12).
    Route::post('/expedientes/{expediente}/validacion', [ValidacionController::class, 'store']);
    Route::patch('/expedientes/{expediente}/documentos-completos', [ExpedienteController::class, 'marcarDocumentosCompletos']);

    // Fase 5 — Gestor de plantillas (HU-39/40) y selección vigente (HU-41, RN-13).
    Route::get('/tipos-documento-plantilla', [PlantillaController::class, 'tipos']);
    Route::get('/plantillas', [PlantillaController::class, 'index']);
    Route::post('/plantillas', [PlantillaController::class, 'store']);
    Route::patch('/plantillas/{plantilla}', [PlantillaController::class, 'update']);
    Route::delete('/plantillas/{plantilla}', [PlantillaController::class, 'destroy']);
    Route::get('/plantilla-seleccion', [PlantillaSeleccionController::class, 'index']);
    Route::post('/plantilla-seleccion', [PlantillaSeleccionController::class, 'store']);

    // Endpoint de humo (Fase 0): confirma API + BD importada.
    Route::get('/ping', function () {
        return response()->json([
            'ok' => true,
            'db' => Facultad::count(),
        ]);
    });

    // Usuario autenticado actual (con el login simulado).
    Route::get('/me', function (Request $request) {
        $usuario = $request->user();

        if (! $usuario) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        return response()->json($usuario->only('id', 'nombre', 'email', 'rol'));
    });

    // Endpoints de desarrollo: solo se registran en entorno local.
    if (app()->environment('local')) {
        Route::get('/dev/usuarios', function () {
            return response()->json(
                Usuario::where('activo', true)
                    ->orderBy('id')
                    ->get(['id', 'nombre', 'email', 'rol'])
            );
        });
    }
});
