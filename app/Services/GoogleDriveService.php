<?php

namespace App\Services;

use App\Models\DocumentoGenerado;
use App\Models\GoogleDriveConnection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GoogleDriveService
{
    private const DOCX_MIME = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    private const DRIVE_SCOPE = 'https://www.googleapis.com/auth/drive';

    public function isConnected(): bool
    {
        return GoogleDriveConnection::query()->whereKey(1)->exists();
    }

    public function folderUrl(string $tipo = 'CARTA_VRIN'): string
    {
        return 'https://drive.google.com/drive/folders/'.$this->folderId($tipo);
    }

    public function authorizationUrl(): string
    {
        $this->ensureConfigured();

        $state = Str::random(64);
        Cache::put('google_drive_oauth_'.$state, true, now()->addMinutes(10));

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => config('services.google_drive.redirect'),
            'response_type' => 'code',
            'scope' => self::DRIVE_SCOPE.' openid email',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'login_hint' => config('services.google_drive.account_email'),
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    public function completeAuthorization(string $state, string $code): void
    {
        $this->ensureConfigured();

        if (! Cache::pull('google_drive_oauth_'.$state)) {
            throw new HttpException(400, 'La autorización de Drive expiró. Iníciala de nuevo desde el sistema.');
        }

        $response = Http::asForm()->timeout(20)->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => config('services.google_drive.redirect'),
            'grant_type' => 'authorization_code',
            'code' => $code,
        ]);

        $refreshToken = $response->json('refresh_token');
        $accessToken = $response->json('access_token');
        if (! $response->successful() || ! is_string($refreshToken) || ! is_string($accessToken)) {
            throw new HttpException(502, 'Google no entregó autorización permanente para Drive. Vuelve a conectar la cuenta.');
        }

        $this->assertAccountEmail($accessToken);
        $this->assertFolderWritable($accessToken);

        GoogleDriveConnection::query()->updateOrCreate(
            ['id' => 1],
            ['refresh_token' => $refreshToken, 'connected_at' => now()]
        );
    }

    /** @return array{id: string, url: string, already_synced: bool} */
    public function upload(DocumentoGenerado $documento): array
    {
        $folderId = $this->folderId($documento->tipo);

        return Cache::lock('google_drive_documento_'.$documento->id, 90)->block(10, function () use ($documento, $folderId): array {
            $documento->refresh();
            if ($documento->drive_file_id && $documento->drive_folder_id === $folderId) {
                return $this->fileResult($documento->drive_file_id, true);
            }

            $rutaBase = realpath(storage_path('app'));
            $ruta = realpath(storage_path('app/'.$documento->docx_path));
            if (! $rutaBase || ! $ruta || ! str_starts_with($ruta, $rutaBase.DIRECTORY_SEPARATOR) || ! is_file($ruta)) {
                throw new HttpException(404, 'El documento Word no se encuentra en el servidor.');
            }
            if ($documento->sha256 && hash_file('sha256', $ruta) !== $documento->sha256) {
                throw new HttpException(409, 'El documento Word cambió en el servidor. No se subió a Drive.');
            }

            $accessToken = $this->accessToken();
            $boundary = 'vrin_'.Str::random(24);
            $metadata = json_encode([
                'name' => $documento->nombreArchivo(),
                'mimeType' => self::DOCX_MIME,
                'parents' => [$folderId],
                'appProperties' => ['vrin_documento_id' => (string) $documento->id],
            ], JSON_THROW_ON_ERROR);
            $contenido = file_get_contents($ruta);
            if ($contenido === false) {
                throw new HttpException(500, 'No se pudo leer el documento Word en el servidor.');
            }
            $body = "--{$boundary}\r\nContent-Type: application/json; charset=UTF-8\r\n\r\n"
                .$metadata."\r\n--{$boundary}\r\nContent-Type: ".self::DOCX_MIME."\r\n\r\n"
                .$contenido."\r\n--{$boundary}--\r\n";

            $response = Http::withToken($accessToken)
                ->withBody($body, 'multipart/related; boundary='.$boundary)
                ->timeout(60)
                ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id&supportsAllDrives=true');

            $id = $response->json('id');
            if (! $response->successful() || ! is_string($id) || $id === '') {
                throw new HttpException(502, 'Google Drive no pudo guardar el documento. Comprueba el acceso a la carpeta.');
            }

            $documento->drive_file_id = $id;
            $documento->drive_folder_id = $folderId;
            $documento->drive_synced_at = now();
            $documento->save();

            return $this->fileResult($id, false);
        });
    }

    private function ensureConfigured(): void
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')
            || ! config('services.google_drive.redirect') || ! config('services.google_drive.folder_id')
            || ! config('services.google_drive.resolution_folder_id')
            || ! config('services.google_drive.account_email')) {
            throw new HttpException(503, 'Falta configurar Google Drive en Laravel.');
        }
    }

    private function folderId(string $tipo): string
    {
        return match ($tipo) {
            'CARTA_VRIN' => (string) config('services.google_drive.folder_id'),
            'RESOLUCION' => (string) config('services.google_drive.resolution_folder_id'),
            default => throw new HttpException(422, 'Este tipo de documento no se puede guardar en Drive.'),
        };
    }

    private function assertAccountEmail(string $accessToken): void
    {
        $response = Http::withToken($accessToken)->timeout(20)
            ->get('https://openidconnect.googleapis.com/v1/userinfo');
        $correoEsperado = strtolower((string) config('services.google_drive.account_email'));
        $correoRecibido = strtolower((string) $response->json('email'));

        if (! $response->successful() || $response->json('email_verified') !== true
            || $correoRecibido !== $correoEsperado) {
            throw new HttpException(403, 'Debes autorizar Google Drive con la cuenta '.$correoEsperado.'.');
        }
    }

    private function assertFolderWritable(string $accessToken): void
    {
        foreach (['CARTA_VRIN' => 'cartas', 'RESOLUCION' => 'resoluciones'] as $tipo => $nombre) {
            $folderId = $this->folderId($tipo);
            $response = Http::withToken($accessToken)->timeout(20)->get(
                "https://www.googleapis.com/drive/v3/files/{$folderId}",
                ['fields' => 'id,mimeType,capabilities(canAddChildren)', 'supportsAllDrives' => 'true']
            );

            if (! $response->successful() || $response->json('mimeType') !== 'application/vnd.google-apps.folder'
                || ! $response->json('capabilities.canAddChildren')) {
                throw new HttpException(403, "La cuenta de Google no puede agregar archivos a la carpeta de {$nombre}.");
            }
        }
    }

    private function accessToken(): string
    {
        $this->ensureConfigured();
        $connection = GoogleDriveConnection::query()->find(1);
        if (! $connection) {
            throw new HttpException(503, 'Conecta la cuenta institucional de Google Drive antes de descargar la carta.');
        }

        $response = Http::asForm()->timeout(20)->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $connection->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        $accessToken = $response->json('access_token');
        if (! $response->successful() || ! is_string($accessToken)) {
            throw new HttpException(503, 'La conexión con Google Drive expiró. Un administrador debe conectarla de nuevo.');
        }

        return $accessToken;
    }

    /** @return array{id: string, url: string, already_synced: bool} */
    private function fileResult(string $id, bool $alreadySynced): array
    {
        return [
            'id' => $id,
            'url' => 'https://drive.google.com/file/d/'.$id.'/view',
            'already_synced' => $alreadySynced,
        ];
    }
}
