<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facultad extends Model
{
    protected $table = 'facultades';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function escuelas()
    {
        return $this->hasMany(Escuela::class, 'facultad_id');
    }
}
