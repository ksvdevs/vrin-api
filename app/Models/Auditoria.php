<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditoria';

    // Append-only (D-20): solo created_at, nunca se actualiza; y el sello lo
    // fija AuditoriaService con milisegundos, no los timestamps de Eloquent.
    public $timestamps = false;

    // Los casts `datetime` serializan con esta precisión (DATETIME(3) en BD).
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
        'created_at',
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
