<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plantilla extends Model
{
    protected $table = 'plantillas';

    protected $fillable = [
        'codigo',
        'nombre',
        'modulo',
        'tipo_documento_id',
        'version',
        'archivo_path',
        'sha256',
        'tokens',
        'estado',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tokens' => 'array',
        ];
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumentoPlantilla::class, 'tipo_documento_id');
    }

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'created_by');
    }

    public function documentosGenerados()
    {
        return $this->hasMany(DocumentoGenerado::class, 'plantilla_id');
    }
}
