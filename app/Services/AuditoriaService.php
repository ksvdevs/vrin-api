<?php

namespace App\Services;

use App\Models\Auditoria;

/**
 * Registro de auditoría (append-only, tabla `auditoria` sin FKs, D-20).
 *
 * Escritor de bajo nivel: lo invoca el listener `EscribirAuditoria` cuando se
 * dispara un evento de dominio. Ningún controlador ni servicio de negocio lo
 * usa directamente.
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
        ]);
    }
}
