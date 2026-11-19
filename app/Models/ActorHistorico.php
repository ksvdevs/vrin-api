<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActorHistorico extends Model
{
    protected $table = 'actores_historicos';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['id', 'nombres', 'apellidos', 'rol_nombre', 'fecha_alta', 'archivado_at'];

    protected function casts(): array
    {
        return ['fecha_alta' => 'datetime', 'archivado_at' => 'datetime'];
    }

    public function getNombreAttribute(): string
    {
        return trim($this->nombres.' '.$this->apellidos);
    }
}
