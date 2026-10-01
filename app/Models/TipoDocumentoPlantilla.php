<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoDocumentoPlantilla extends Model
{
    protected $table = 'tipos_documento_plantilla';

    protected $fillable = [
        'codigo',
        'nombre',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function plantillas()
    {
        return $this->hasMany(Plantilla::class, 'tipo_documento_id');
    }
}
