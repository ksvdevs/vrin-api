<?php

namespace App\Policies;

use App\Models\Expediente;
use App\Models\Usuario;

class ExpedientePolicy
{
    // Solo Secretaría y Administrador registran expedientes (Calidad no registra, RN-01).
    private const ROLES_REGISTRO = ['SECRETARIA', 'ADMINISTRADOR'];

    // La bandeja y el detalle los consultan los tres roles del sistema.
    private const ROLES_CONSULTA = ['ADMINISTRADOR', 'SECRETARIA', 'CALIDAD'];

    public function viewAny(Usuario $user): bool
    {
        return in_array($user->rol, self::ROLES_CONSULTA, true);
    }

    public function view(Usuario $user, Expediente $expediente): bool
    {
        return in_array($user->rol, self::ROLES_CONSULTA, true);
    }

    public function create(Usuario $user): bool
    {
        return in_array($user->rol, self::ROLES_REGISTRO, true);
    }

    public function subirArchivo(Usuario $user, Expediente $expediente): bool
    {
        return in_array($user->rol, self::ROLES_REGISTRO, true);
    }

    // RN-01: validar requisitos es exclusivo de Calidad. La precondición
    // documentos_completos (RN-12) la controla el workflow como 409, para que
    // un OBSERVADO reciba un mensaje de estado claro y no un 403 genérico.
    public function validarRequisitos(Usuario $user, Expediente $expediente): bool
    {
        return $user->rol === 'CALIDAD';
    }

    // Subsanación (RN-12): Secretaría/Admin completa los documentos de un OBSERVADO.
    public function marcarDocumentosCompletos(Usuario $user, Expediente $expediente): bool
    {
        return in_array($user->rol, self::ROLES_REGISTRO, true);
    }
}
