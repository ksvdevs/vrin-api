<?php

namespace App\Policies;

use App\Models\Docente;
use App\Models\Usuario;

class DocentePolicy
{
    private const ROL_ADMINISTRADOR = 'ADMINISTRADOR_GENERAL';

    private const ROLES_LECTURA = ['ADMINISTRADOR_GENERAL', 'SECRETARIA', 'CALIDAD'];

    public function viewAny(Usuario $user): bool
    {
        return in_array($user->rol_codigo, self::ROLES_LECTURA, true);
    }

    public function view(Usuario $user, Docente $docente): bool
    {
        return in_array($user->rol_codigo, self::ROLES_LECTURA, true);
    }

    public function create(Usuario $user): bool
    {
        return $user->rol_codigo === self::ROL_ADMINISTRADOR;
    }

    public function update(Usuario $user, Docente $docente): bool
    {
        return $user->rol_codigo === self::ROL_ADMINISTRADOR;
    }

    public function delete(Usuario $user, Docente $docente): bool
    {
        return $user->rol_codigo === self::ROL_ADMINISTRADOR;
    }
}
