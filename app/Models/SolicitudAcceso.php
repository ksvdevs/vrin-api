<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudAcceso extends Model
{
    protected $table = 'solicitudes_acceso';

    protected $fillable = [
        'nombres',
        'apellidos',
        'dni',
        'email',
        'dependencia',
        'cargo',
        'motivo',
        'estado',
        'revisado_por',
        'fecha_revision',
    ];

    protected function casts(): array
    {
        return [
            'fecha_revision' => 'datetime',
        ];
    }

    public function revisor()
    {
        return $this->belongsTo(Usuario::class, 'revisado_por');
    }
}
