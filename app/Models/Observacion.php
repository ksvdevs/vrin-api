<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Observacion extends Model
{
    protected $table = 'observaciones';

    // La tabla solo tiene created_at.
    const UPDATED_AT = null;

    protected $fillable = [
        'expediente_id',
        'etapa',
        'origen',
        'texto',
        'resuelta_at',
        'creada_por',
    ];

    protected function casts(): array
    {
        return [
            'resuelta_at' => 'datetime',
        ];
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'creada_por');
    }
}
