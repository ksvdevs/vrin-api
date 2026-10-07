<?php

namespace App\Models;

use App\Services\PlazoRendicionService;
use Illuminate\Database\Eloquent\Model;

class Rendicion extends Model
{
    protected $table = 'rendiciones';

    protected $primaryKey = 'expediente_id';

    public $incrementing = false;

    protected $fillable = [
        'expediente_id',
        'fecha_desembolso',
        'monto_desembolsado',
        'fecha_limite',
        'fecha_informe',
        'estado',
        'con_retraso',
        'cerrada_por',
        'cerrada_at',
    ];

    protected $appends = ['dias_habiles_restantes'];

    public function getDiasHabilesRestantesAttribute(): int
    {
        if ($this->estado === 'CERRADA') {
            return 0;
        }

        return app(PlazoRendicionService::class)->diasHabilesRestantes($this);
    }

    protected function casts(): array
    {
        return [
            'fecha_desembolso' => 'date',
            'monto_desembolsado' => 'decimal:2',
            'fecha_limite' => 'date',
            'fecha_informe' => 'date',
            'con_retraso' => 'boolean',
            'cerrada_at' => 'datetime',
        ];
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function cerrador()
    {
        return $this->belongsTo(Usuario::class, 'cerrada_por');
    }
}
