<?php

namespace App\Policies;

use App\Models\Expediente;
use App\Models\Usuario;

class ExpedientePolicy
{
    // Solo Secretaría y Administrador registran expedientes (Calidad no registra, RN-01).
    private const ROLES_REGISTRO = ['SECRETARIA', 'ADMINISTRADOR_GENERAL'];

    // La bandeja y el detalle los consultan los tres roles del sistema.
    private const ROLES_CONSULTA = ['ADMINISTRADOR_GENERAL', 'SECRETARIA', 'CALIDAD'];

    public function viewAny(Usuario $user): bool
    {
        return in_array($user->rol_codigo, self::ROLES_CONSULTA, true);
    }

    public function view(Usuario $user, Expediente $expediente): bool
    {
        return in_array($user->rol_codigo, self::ROLES_CONSULTA, true);
    }

    // Validación de requisitos: exclusivo de Calidad (RN-01). Las
    // precondiciones de estado/documentos las valida el workflow (409).
    public function validarRequisitos(Usuario $user, Expediente $expediente): bool
    {
        return $user->rol_codigo === 'CALIDAD';
    }

    // Subsanación de un expediente observado (RN-12).
    public function subsanar(Usuario $user, Expediente $expediente): bool
    {
        return in_array($user->rol_codigo, self::ROLES_REGISTRO, true);
    }

    // Fase 6 — Generar Carta VRIN→OPP y registrar la respuesta OPP (SEC/ADMIN).
    public function generarCarta(Usuario $user, Expediente $expediente): bool
    {
        return in_array($user->rol_codigo, self::ROLES_REGISTRO, true);
    }

    public function registrarOpp(Usuario $user, Expediente $expediente): bool
    {
        return in_array($user->rol_codigo, self::ROLES_REGISTRO, true);
    }

    // Sugerencia del siguiente número de carta del año (RN-10): SEC/ADMIN.
    public function sugerirCarta(Usuario $user): bool
    {
        return in_array($user->rol_codigo, self::ROLES_REGISTRO, true);
    }

    public function generarResolucion(Usuario $user, Expediente $expediente): bool
    {
        return in_array($user->rol_codigo, self::ROLES_REGISTRO, true);
    }

    public function sugerirResolucion(Usuario $user): bool
    {
        return in_array($user->rol_codigo, self::ROLES_REGISTRO, true);
    }

    public function anularDocumento(Usuario $user, Expediente $expediente): bool
    {
        return in_array($user->rol_codigo, self::ROLES_REGISTRO, true);
    }

    public function registrarDesembolso(Usuario $user, Expediente $expediente): bool
    {
        return in_array($user->rol_codigo, self::ROLES_REGISTRO, true);
    }

    public function actualizarFechaLimite(Usuario $user, Expediente $expediente): bool
    {
        return in_array($user->rol_codigo, self::ROLES_REGISTRO, true);
    }

    public function actualizarDoi(Usuario $user, Expediente $expediente): bool
    {
        return in_array($user->rol_codigo, self::ROLES_REGISTRO, true);
    }

    public function cerrarRendicion(Usuario $user, Expediente $expediente): bool
    {
        return in_array($user->rol_codigo, self::ROLES_REGISTRO, true);
    }

    public function create(Usuario $user): bool
    {
        return in_array($user->rol_codigo, self::ROLES_REGISTRO, true);
    }

    public function subirArchivo(Usuario $user, Expediente $expediente): bool
    {
        return in_array($user->rol_codigo, self::ROLES_REGISTRO, true);
    }

    public function update(Usuario $user, Expediente $expediente): bool
    {
        return in_array($user->rol_codigo, self::ROLES_REGISTRO, true);
    }

    public function delete(Usuario $user, Expediente $expediente): bool
    {
        return in_array($user->rol_codigo, self::ROLES_REGISTRO, true);
    }
}
