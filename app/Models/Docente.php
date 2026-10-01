<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Docente extends Model
{
    use SoftDeletes;

    protected $table = 'docentes';

    protected $fillable = [
        'dni',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'grado',
        'tipo_contrato',
        'escuela_id',
        'email',
        'activo',
        'origen',
        'external_id',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'synced_at' => 'datetime',
        ];
    }

    public function escuela()
    {
        return $this->belongsTo(Escuela::class, 'escuela_id');
    }

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'docente_id');
    }
}
