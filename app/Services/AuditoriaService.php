<?php

namespace App\Services;

use App\Models\Auditoria;

/**
 * Registro de auditoría (append-only, tabla `auditoria` sin FKs).
 *
 * Único punto de escritura de la tabla (D-20): solo los listeners de
 * eventos de dominio (app/Listeners) lo invocan. El sello `created_at` se
 * fija aquí con precisión de milisegundos (la columna es DATETIME(3)).
 */
class AuditoriaService
{
    /**
     * Inserta un evento en `auditoria`.
     *
     * Contexto opcional: expediente_id, usuario_id (null = sistema),
     * entidad, entidad_id, antes (array), despues (array), ip.
     */
    public function registrar(string $accion, array $contexto = []): Auditoria
    {
        return Auditoria::create([
            'expediente_id' => $contexto['expediente_id'] ?? null,
            'usuario_id' => $contexto['usuario_id'] ?? auth()->id(),
            'accion' => $accion,
            'entidad' => $contexto['entidad'] ?? null,
            'entidad_id' => $contexto['entidad_id'] ?? null,
            'antes' => $contexto['antes'] ?? null,
            'despues' => $contexto['despues'] ?? null,
            'ip' => $contexto['ip'] ?? request()?->ip(),
            'created_at' => now()->format('Y-m-d H:i:s.v'),
        ]);
    }
}
