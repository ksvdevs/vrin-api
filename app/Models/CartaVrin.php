<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartaVrin extends Model
{
    protected $table = 'cartas_vrin';

    protected $primaryKey = 'expediente_id';

    public $incrementing = false;

    protected $fillable = [
        'expediente_id',
        'numero',
        'anio',
        'fecha',
        'ciudad',
        'estado',
        'emitida_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function emisor()
    {
        return $this->belongsTo(Usuario::class, 'emitida_por');
    }
}
