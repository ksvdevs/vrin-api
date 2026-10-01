<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantillaSeleccionada extends Model
{
    protected $table = 'plantilla_seleccionada';

    // PK compuesta (modulo, tipo_documento_id): el modelo es de lectura/upsert
    // por where(), no por find(). No hay PK simple ni incrementing.
    protected $primaryKey = null;

    public $incrementing = false;

    // La tabla no tiene created_at/updated_at; su sello es seleccionado_at.
    public $timestamps = false;

    protected $fillable = [
        'modulo',
        'tipo_documento_id',
        'plantilla_id',
        'seleccionado_por',
        'seleccionado_at',
    ];

    protected function casts(): array
    {
        return [
            'seleccionado_at' => 'datetime',
        ];
    }

    public function plantilla()
    {
        return $this->belongsTo(Plantilla::class, 'plantilla_id');
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumentoPlantilla::class, 'tipo_documento_id');
    }

    public function seleccionador()
    {
        return $this->belongsTo(Usuario::class, 'seleccionado_por');
    }
}
