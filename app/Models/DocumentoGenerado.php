<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoGenerado extends Model
{
    protected $table = 'documentos_generados';

    // La tabla solo tiene created_at; su sello de emisión es generado_at.
    const UPDATED_AT = null;

    protected $fillable = [
        'expediente_id',
        'tipo',
        'version',
        'plantilla_id',
        'datos',
        'docx_path',
        'pdf_path',
        'sha256',
        'es_vigente',
        'generado_por',
        'generado_at',
    ];

    protected function casts(): array
    {
        return [
            'datos' => 'array',
            'es_vigente' => 'boolean',
            'generado_at' => 'datetime',
        ];
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function plantilla()
    {
        return $this->belongsTo(Plantilla::class, 'plantilla_id');
    }

    public function generador()
    {
        return $this->belongsTo(Usuario::class, 'generado_por');
    }
}
