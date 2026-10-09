<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartaVrinController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\DocumentoGeneradoController;
use App\Http\Controllers\EscuelaController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\FacultadController;
use App\Http\Controllers\OcrController;
use App\Http\Controllers\PlantillaController;
use App\Http\Controllers\PlantillaSeleccionController;
use App\Http\Controllers\RendicionController;
use App\Http\Controllers\ResolucionController;
use App\Http\Controllers\RespuestaOppController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ValidacionController;
use App\Models\Facultad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/usuarios/validar-dni', [UsuarioController::class, 'validarDni']);
    Route::apiResource('usuarios', UsuarioController::class);
    Route::apiResource('roles', RolController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['roles' => 'rol']);

    // Fase 1: catálogos en cascada (facultades con escuelas activas).
    Route::get('/facultades', [FacultadController::class, 'index']);
    Route::post('/facultades', [FacultadController::class, 'store']);
    Route::post('/escuelas', [EscuelaController::class, 'store']);

    // Fase 1: búsqueda (?q=) y CRUD de docentes (destroy = baja lógica).
    Route::apiResource('docentes', DocenteController::class);

    // Fase 2 — Etapa 1: registro de expediente (sin OCR) y carta del docente.
    Route::post('/expedientes', [ExpedienteController::class, 'store']);
    Route::post('/expedientes/{expediente}/archivos', [ExpedienteController::class, 'subirArchivo']);

    // Fase 9 — OCR síncrono: extrae metadatos de la carta escaneada del docente.
    Route::post('/articulos/ocr', [OcrController::class, 'extraer']);

    // Fase 3 — Bandeja de expedientes y vista detalle.
    Route::get('/panel-control', [DashboardController::class, 'index']);
    Route::get('/panel-control/reporte', [DashboardController::class, 'reporte']);
    Route::get('/expedientes', [ExpedienteController::class, 'index']);
    Route::get('/expedientes/{expediente}', [ExpedienteController::class, 'show']);
    Route::put('/expedientes/{expediente}', [ExpedienteController::class, 'update']);
    Route::delete('/expedientes/{expediente}', [ExpedienteController::class, 'destroy']);
    Route::get('/expedientes/{expediente}/archivos/{archivo}', [ExpedienteController::class, 'archivo']);
    Route::delete('/expedientes/{expediente}/archivos/{archivo}', [ExpedienteController::class, 'retirarComprobante']);

    // Fase 4 — Validación de Calidad (RN-01/RN-02) y subsanación (RN-12).
    Route::post('/expedientes/{expediente}/validacion', [ValidacionController::class, 'store']);
    Route::patch('/expedientes/{expediente}/documentos-completos', [ExpedienteController::class, 'marcarDocumentosCompletos']);

    // Fase 5 — Gestor de plantillas, selección vigente (RN-13) y generación documental.
    Route::get('/plantillas', [PlantillaController::class, 'index']);
    Route::post('/plantillas', [PlantillaController::class, 'store']);
    Route::patch('/plantillas/{plantilla}', [PlantillaController::class, 'update']);
    Route::delete('/plantillas/{plantilla}', [PlantillaController::class, 'destroy']);
    Route::get('/plantilla-seleccion', [PlantillaSeleccionController::class, 'index']);
    Route::post('/plantilla-seleccion', [PlantillaSeleccionController::class, 'store']);

    // Fase 6 — Etapa 2: Carta VRIN→OPP (RN-09/RN-10/RN-13) y respuesta OPP (RN-06).
    Route::get('/cartas-vrin/sugerencia', [CartaVrinController::class, 'sugerencia']);
    Route::post('/expedientes/{expediente}/carta-vrin', [CartaVrinController::class, 'store']);
    Route::post('/expedientes/{expediente}/carta-vrin/preview', [CartaVrinController::class, 'preview']);
    Route::put('/expedientes/{expediente}/carta-vrin', [CartaVrinController::class, 'update']);
    Route::post('/expedientes/{expediente}/respuesta-opp', [RespuestaOppController::class, 'store']);
    Route::put('/expedientes/{expediente}/respuesta-opp', [RespuestaOppController::class, 'update']);
    Route::post('/expedientes/{expediente}/respuesta-opp/ocr', [OcrController::class, 'extraerCartaOpp']);
    Route::get('/expedientes/{expediente}/documentos/{documentoGenerado}', [ExpedienteController::class, 'documento']);

    // Fase 7 — Etapa 3: Resolución (RN-08, RN-10, RN-13) y anulación.
    Route::get('/resoluciones/sugerencia', [ResolucionController::class, 'sugerencia']);
    Route::post('/expedientes/{expediente}/resolucion/generar', [ResolucionController::class, 'generar']);
    Route::put('/expedientes/{expediente}/resolucion', [ResolucionController::class, 'update']);
    Route::post('/expedientes/{expediente}/resolucion/preview', [ResolucionController::class, 'preview']);
    Route::post('/expedientes/{expediente}/documentos/{documentoGenerado}/anular', [DocumentoGeneradoController::class, 'anular']);

    // Fase 8 — Etapa 4: Rendición.
    Route::post('/expedientes/{expediente}/rendicion/desembolso', [RendicionController::class, 'registrarDesembolso']);
    Route::patch('/expedientes/{expediente}/rendicion/fecha-limite', [RendicionController::class, 'actualizarFechaLimite']);
    Route::post('/expedientes/{expediente}/rendicion/doi', [RendicionController::class, 'actualizarDoi']);
    Route::post('/expedientes/{expediente}/rendicion/cerrar', [RendicionController::class, 'cerrarRendicion']);

    // Usuario autenticado actual.
    Route::get('/me', function (Request $request) {
        $usuario = $request->user();

        if (! $usuario) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $usuario->loadMissing('rolRef');

        return response()->json($usuario->only(
            'id', 'dni', 'nombres', 'apellidos', 'nombre', 'email',
            'rol', 'rol_codigo', 'rol_id', 'activo', 'ultimo_login_at',
        ));
    });
});

// Endpoint de humo (Fase 0): confirma API + BD importada.
Route::get('/ping', function () {
    return response()->json([
        'ok' => true,
        'db' => Facultad::count(),
    ]);
});
