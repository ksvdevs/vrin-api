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
        'codigo_verificacion',
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
            'drive_synced_at' => 'datetime',
        ];
    }

    public function nombreArchivo(string $extension = 'docx'): string
    {
        if ($this->tipo === 'RESOLUCION') {
            $numero = $this->datos['numero_resolucion'] ?? null;
            $anio = $this->datos['anio'] ?? null;
            if (is_scalar($numero) && preg_match('/^\d{1,6}$/D', (string) $numero)
                && is_scalar($anio) && preg_match('/^\d{4}$/D', (string) $anio)) {
                return "RESOLUCION_{$numero}-{$anio}_v{$this->version}.{$extension}";
            }
        }

        if ($this->tipo !== 'CARTA_VRIN') {
            return basename($this->docx_path, '.docx').'.'.$extension;
        }

        $numero = $this->datos['NUMERO_CARTA_VRIN'] ?? null;
        $numeroSeguro = is_string($numero) && preg_match('/^\d{1,6}-\d{4}$/D', $numero)
            ? '_'.$numero
            : '';

        return "CARTA_VRIN{$numeroSeguro}_v{$this->version}.{$extension}";
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function plantilla()
    {
        return $this->belongsTo(Plantilla::class, 'plantilla_id')->withTrashed();
    }

    public function generador()
    {
        return $this->belongsTo(Usuario::class, 'generado_por');
    }
}
