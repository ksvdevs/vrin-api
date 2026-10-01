<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    // La tabla no tiene password: la autenticación final es Google (Fase 11, D-06).
    protected $fillable = [
        'email',
        'nombre',
        'rol',
        'google_sub',
        'activo',
        'ultimo_login_at',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'ultimo_login_at' => 'datetime',
        ];
    }

    public function solicitudesRevisadas()
    {
        return $this->hasMany(SolicitudAcceso::class, 'revisado_por');
    }

    public function expedientesCreados()
    {
        return $this->hasMany(Expediente::class, 'created_by');
    }

    public function expedientesEditados()
    {
        return $this->hasMany(Expediente::class, 'updated_by');
    }

    public function validacionesCalidad()
    {
        return $this->hasMany(ValidacionCalidad::class, 'validado_por');
    }

    public function cartasEmitidas()
    {
        return $this->hasMany(CartaVrin::class, 'emitida_por');
    }

    public function respuestasOppRegistradas()
    {
        return $this->hasMany(RespuestaOpp::class, 'registrado_por');
    }

    public function resolucionesEmitidas()
    {
        return $this->hasMany(Resolucion::class, 'emitida_por');
    }

    public function rendicionesCerradas()
    {
        return $this->hasMany(Rendicion::class, 'cerrada_por');
    }

    public function archivosSubidos()
    {
        return $this->hasMany(Archivo::class, 'subido_por');
    }

    public function plantillasCreadas()
    {
        return $this->hasMany(Plantilla::class, 'created_by');
    }

    public function documentosGenerados()
    {
        return $this->hasMany(DocumentoGenerado::class, 'generado_por');
    }

    public function observacionesCreadas()
    {
        return $this->hasMany(Observacion::class, 'creada_por');
    }
}
