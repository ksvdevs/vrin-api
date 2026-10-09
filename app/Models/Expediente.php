<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expediente extends Model
{
    use SoftDeletes;

    protected $table = 'expedientes';

    protected $fillable = [
        'codigo',
        'modulo',
        'docente_id',
        'grado',
        'tipo_contrato',
        'escuela_id',
        'carta_docente_numero',
        'carta_docente_registro_numero',
        'carta_docente_registro_fecha',
        'carta_docente_fecha',
        'registro_mp_numero',
        'documentos_completos',
        'estado',
        'etapa_actual',
        'resolucion_borrador',
        'cerrado_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'carta_docente_fecha' => 'date',
            'carta_docente_registro_fecha' => 'date',
            'documentos_completos' => 'boolean',
            'cerrado_at' => 'datetime',
            'resolucion_borrador' => 'array',
        ];
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function escuela()
    {
        return $this->belongsTo(Escuela::class, 'escuela_id');
    }

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(Usuario::class, 'updated_by');
    }

    // Detalle del módulo Artículos (1:1)
    public function articulo()
    {
        return $this->hasOne(ExpedienteArticulo::class, 'expediente_id');
    }

    // Etapas (1:1, PK = expediente_id)
    public function validacionCalidad()
    {
        return $this->hasOne(ValidacionCalidad::class, 'expediente_id');
    }

    public function cartaVrin()
    {
        return $this->hasOne(CartaVrin::class, 'expediente_id');
    }

    public function respuestaOpp()
    {
        return $this->hasOne(RespuestaOpp::class, 'expediente_id');
    }

    public function resolucion()
    {
        return $this->hasOne(Resolucion::class, 'expediente_id');
    }

    public function rendicion()
    {
        return $this->hasOne(Rendicion::class, 'expediente_id');
    }

    public function archivos()
    {
        return $this->hasMany(Archivo::class, 'expediente_id');
    }

    public function observaciones()
    {
        return $this->hasMany(Observacion::class, 'expediente_id');
    }

    public function documentosGenerados()
    {
        return $this->hasMany(DocumentoGenerado::class, 'expediente_id');
    }

    public function auditoria()
    {
        return $this->hasMany(Auditoria::class, 'expediente_id');
    }
}
