<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Archivo extends Model
{
    use SoftDeletes;

    protected $table = 'archivos';

    // La tabla solo tiene created_at y deleted_at.
    const UPDATED_AT = null;

    protected $fillable = [
        'expediente_id',
        'etapa',
        'tipo',
        'descripcion',
        'nombre_original',
        'storage_path',
        'mime',
        'tamano_bytes',
        'sha256',
        'ocr_json',
        'ocr_confianza',
        'subido_por',
    ];

    protected function casts(): array
    {
        return [
            'ocr_json' => 'array',
            'ocr_confianza' => 'decimal:1',
        ];
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function subidor()
    {
        return $this->belongsTo(Usuario::class, 'subido_por');
    }
}
