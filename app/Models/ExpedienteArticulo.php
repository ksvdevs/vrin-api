<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpedienteArticulo extends Model
{
    protected $table = 'expediente_articulos';

    protected $primaryKey = 'expediente_id';

    public $incrementing = false;

    protected $fillable = [
        'expediente_id',
        'titulo',
        'revista',
        'base_indexadora',
        'cuartil',
        'monto_solicitado',
        'doi',
        'fecha_aceptacion',
    ];

    protected function casts(): array
    {
        return [
            'monto_solicitado' => 'decimal:2',
            'fecha_aceptacion' => 'date',
        ];
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }
}
