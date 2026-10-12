<?php

namespace App\Http\Controllers;

use App\Models\DocumentoGenerado;
use App\Models\Expediente;
use App\Services\GoogleDriveService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GoogleDriveController extends Controller
{
    use AuthorizesRequests;

    public function status(GoogleDriveService $drive): JsonResponse
    {
        return response()->json([
            'connected' => $drive->isConnected(),
            'folder_url' => $drive->folderUrl(),
            'resolution_folder_url' => $drive->folderUrl('RESOLUCION'),
        ]);
    }

    public function authorization(Request $request, GoogleDriveService $drive): JsonResponse
    {
        abort_unless($request->user()?->rol_codigo === 'ADMINISTRADOR_GENERAL', 403);

        return response()->json(['authorization_url' => $drive->authorizationUrl()]);
    }

    public function callback(Request $request, GoogleDriveService $drive): Response
    {
        if ($request->query('error')) {
            throw new HttpException(400, 'Google canceló la autorización de Drive.');
        }

        $datos = $request->validate([
            'state' => ['required', 'string', 'size:64'],
            'code' => ['required', 'string'],
        ]);
        $drive->completeAuthorization($datos['state'], $datos['code']);

        return response('Google Drive conectado. Vuelve al sistema y pulsa "Comprobar conexión".', 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function upload(Expediente $expediente, DocumentoGenerado $documentoGenerado, GoogleDriveService $drive): JsonResponse
    {
        $this->authorize('view', $expediente);
        abort_unless($documentoGenerado->expediente_id === $expediente->id, 404);

        return response()->json($drive->upload($documentoGenerado));
    }
}
