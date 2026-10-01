<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RespuestaOpp extends Model
{
    protected $table = 'respuestas_opp';

    protected $primaryKey = 'expediente_id';

    public $incrementing = false;

    protected $fillable = [
        'expediente_id',
        'disponibilidad',
        'carta_numero',
        'carta_fecha',
        'monto_aprobado',
        'meta_presupuestal',
        'especifica_gasto',
        'fuente_financiamiento',
        'registro_vrin_numero',
        'registro_vrin_fecha',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'carta_fecha' => 'date',
            'monto_aprobado' => 'decimal:2',
            'registro_vrin_fecha' => 'date',
        ];
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function registrador()
    {
        return $this->belongsTo(Usuario::class, 'registrado_por');
    }
}
