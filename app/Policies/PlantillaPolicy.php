<?php

namespace App\Policies;

use App\Models\Plantilla;
use App\Models\Usuario;

class PlantillaPolicy
{
    // La gestión de plantillas y su selección son exclusivas del administrador.
    private const ROL_GESTOR = 'ADMINISTRADOR';

    public function viewAny(Usuario $user): bool
    {
        return $user->rol === self::ROL_GESTOR;
    }

    public function view(Usuario $user, Plantilla $plantilla): bool
    {
        return $user->rol === self::ROL_GESTOR;
    }

    public function create(Usuario $user): bool
    {
        return $user->rol === self::ROL_GESTOR;
    }

    public function update(Usuario $user, Plantilla $plantilla): bool
    {
        return $user->rol === self::ROL_GESTOR;
    }

    public function delete(Usuario $user, Plantilla $plantilla): bool
    {
        return $user->rol === self::ROL_GESTOR;
    }

    // Selección vigente por módulo/tipo (RN-13, HU-41).
    public function seleccionar(Usuario $user): bool
    {
        return $user->rol === self::ROL_GESTOR;
    }
}
