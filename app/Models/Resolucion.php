<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resolucion extends Model
{
    protected $table = 'resoluciones';

    protected $primaryKey = 'expediente_id';

    public $incrementing = false;

    protected $fillable = [
        'expediente_id',
        'numero',
        'anio',
        'fecha_emision',
        'carta_especialista_investigacion',
        'fecha_carta_especialista_investigacion',
        'estado',
        'emitida_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_carta_especialista_investigacion' => 'date',
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
