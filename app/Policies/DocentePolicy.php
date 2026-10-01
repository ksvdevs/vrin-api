<?php

namespace App\Policies;

use App\Models\Docente;
use App\Models\Usuario;

class DocentePolicy
{
    private const ROL_ADMINISTRADOR = 'ADMINISTRADOR';

    private const ROLES_LECTURA = ['ADMINISTRADOR', 'SECRETARIA', 'CALIDAD'];

    public function viewAny(Usuario $user): bool
    {
        return in_array($user->rol, self::ROLES_LECTURA, true);
    }

    public function view(Usuario $user, Docente $docente): bool
    {
        return in_array($user->rol, self::ROLES_LECTURA, true);
    }

    public function create(Usuario $user): bool
    {
        return $user->rol === self::ROL_ADMINISTRADOR;
    }

    public function update(Usuario $user, Docente $docente): bool
    {
        return $user->rol === self::ROL_ADMINISTRADOR;
    }

    public function delete(Usuario $user, Docente $docente): bool
    {
        return $user->rol === self::ROL_ADMINISTRADOR;
    }
}
