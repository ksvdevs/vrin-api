<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidacionCalidad extends Model
{
    protected $table = 'validaciones_calidad';

    protected $primaryKey = 'expediente_id';

    public $incrementing = false;

    // La tabla no tiene created_at/updated_at; su sello es validado_at.
    public $timestamps = false;

    protected $fillable = [
        'expediente_id',
        'resultado',
        'checklist',
        'observacion',
        'validado_por',
        'validado_at',
    ];

    protected function casts(): array
    {
        return [
            'checklist' => 'array',
            'validado_at' => 'datetime',
        ];
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function validador()
    {
        return $this->belongsTo(Usuario::class, 'validado_por');
    }
}
