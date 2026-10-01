<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditoria';

    // Append-only (D-20): solo created_at, nunca se actualiza.
    const UPDATED_AT = null;

    // La columna es DATETIME(3): sello de servidor con milisegundos (RF-46).
    protected $dateFormat = 'Y-m-d H:i:s.v';

    protected $fillable = [
        'expediente_id',
        'usuario_id',
        'accion',
        'entidad',
        'entidad_id',
        'antes',
        'despues',
        'ip',
    ];

    protected function casts(): array
    {
        return [
            'antes' => 'array',
            'despues' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
