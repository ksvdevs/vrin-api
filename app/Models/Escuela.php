<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Escuela extends Model
{
    protected $table = 'escuelas';

    protected $fillable = [
        'facultad_id',
        'nombre',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'facultad_id');
    }

    public function docentes()
    {
        return $this->hasMany(Docente::class, 'escuela_id');
    }

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'escuela_id');
    }
}
