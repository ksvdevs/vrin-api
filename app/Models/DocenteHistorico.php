<?php

namespace App\Models;

class DocenteHistorico extends Docente
{
    protected $table = 'docentes_historicos';

    public static function sincronizar(Docente $docente): void
    {
        $historico = self::withTrashed()->firstOrNew(['id' => $docente->id]);
        $historico->forceFill($docente->only([
            'dni', 'nombres', 'apellido_paterno', 'apellido_materno', 'genero',
            'grado', 'tipo_contrato', 'categoria', 'celular', 'escuela_id',
            'email', 'activo', 'created_at', 'updated_at', 'deleted_at',
        ]));
        $historico->id = $docente->id;
        $historico->save();
    }
}
