<?php

namespace App\Policies;

use App\Models\Plantilla;
use App\Models\Usuario;

class PlantillaPolicy
{
    private const ROL_ADMINISTRADOR = 'ADMINISTRADOR';

    private const ROLES_LECTURA = ['ADMINISTRADOR', 'SECRETARIA', 'CALIDAD'];

    public function viewAny(Usuario $user): bool
    {
        return in_array($user->rol, self::ROLES_LECTURA, true);
    }

    public function view(Usuario $user, Plantilla $plantilla): bool
    {
        return in_array($user->rol, self::ROLES_LECTURA, true);
    }

    public function create(Usuario $user): bool
    {
        return $user->rol === self::ROL_ADMINISTRADOR;
    }

    public function update(Usuario $user, Plantilla $plantilla): bool
    {
        return $user->rol === self::ROL_ADMINISTRADOR;
    }

    public function delete(Usuario $user, Plantilla $plantilla): bool
    {
        return $user->rol === self::ROL_ADMINISTRADOR;
    }

    /**
     * HU-41 / RN-13: elegir la plantilla vigente de un módulo/tipo es
     * exclusivo del Administrador.
     */
    public function seleccionar(Usuario $user): bool
    {
        return $user->rol === self::ROL_ADMINISTRADOR;
    }
}
