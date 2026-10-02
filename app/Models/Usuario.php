<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'email',
        'dni',
        'nombres',
        'apellidos',
        'rol_id',
        'password_hash',
        'google_sub',
        'activo',
        'ultimo_login_at',
    ];

    protected $appends = [
        'nombre',
        'rol',
        'rol_codigo',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'ultimo_login_at' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function getNombreAttribute(): string
    {
        return trim(($this->attributes['nombres'] ?? '').' '.($this->attributes['apellidos'] ?? ''));
    }

    public function getRolAttribute(): ?string
    {
        return $this->rolRef?->nombre;
    }

    public function getRolCodigoAttribute(): ?string
    {
        $nombre = $this->rolRef?->nombre;

        if ($nombre === null) {
            return null;
        }

        return Str::of($nombre)->slug('_')->upper()->toString();
    }

    public function rolRef(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function expedientesCreados(): HasMany
    {
        return $this->hasMany(Expediente::class, 'created_by');
    }

    public function expedientesEditados(): HasMany
    {
        return $this->hasMany(Expediente::class, 'updated_by');
    }

    public function validacionesCalidad(): HasMany
    {
        return $this->hasMany(ValidacionCalidad::class, 'validado_por');
    }

    public function cartasEmitidas(): HasMany
    {
        return $this->hasMany(CartaVrin::class, 'emitida_por');
    }

    public function respuestasOppRegistradas(): HasMany
    {
        return $this->hasMany(RespuestaOpp::class, 'registrado_por');
    }

    public function resolucionesEmitidas(): HasMany
    {
        return $this->hasMany(Resolucion::class, 'emitida_por');
    }

    public function rendicionesCerradas(): HasMany
    {
        return $this->hasMany(Rendicion::class, 'cerrada_por');
    }

    public function archivosSubidos(): HasMany
    {
        return $this->hasMany(Archivo::class, 'subido_por');
    }

    public function plantillasCreadas(): HasMany
    {
        return $this->hasMany(Plantilla::class, 'created_by');
    }

    public function documentosGenerados(): HasMany
    {
        return $this->hasMany(DocumentoGenerado::class, 'generado_por');
    }

    public function observacionesCreadas(): HasMany
    {
        return $this->hasMany(Observacion::class, 'creada_por');
    }
}
