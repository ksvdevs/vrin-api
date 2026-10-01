<?php

namespace App\Http\Resources;

use App\Models\Expediente;
use App\Models\Usuario;
use App\Services\ExpedienteWorkflow;
use App\Support\EstadoExpediente;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpedienteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $expediente = $this->resource;
        $docente = $expediente->docente;

        return [
            'id' => $expediente->id,
            'codigo' => $expediente->codigo,
            'fecha_registro' => $expediente->created_at?->format('d/m/Y'),
            'docente' => [
                'id' => $docente?->id,
                'nombre_completo' => $docente ? $this->nombreCompleto($docente) : null,
                'dni' => $docente?->dni,
            ],
            'titulo' => $expediente->articulo?->titulo,
            'estado' => $expediente->estado,
            'etapa' => EstadoExpediente::etapa($expediente->estado),
            'badge' => EstadoExpediente::badge($expediente->estado),
            'accion_principal' => $this->accionPrincipal($request->user(), $expediente),
        ];
    }

    private function nombreCompleto(object $docente): string
    {
        return trim(implode(' ', array_filter([
            $docente->grado,
            $docente->nombres,
            $docente->apellido_paterno,
            $docente->apellido_materno,
        ])));
    }

    /**
     * La acción principal la decide el workflow: primera transición disponible
     * (rol + precondición) con acción de UI asociada; si no hay, «Ver Expediente».
     *
     * @return array{clave: string, etiqueta: string}
     */
    private function accionPrincipal(?Usuario $usuario, Expediente $expediente): array
    {
        $workflow = app(ExpedienteWorkflow::class);

        foreach ($workflow->transicionesDisponibles($expediente, $usuario) as $destino) {
            $accion = $workflow->accionDeDestino($expediente->estado, $destino);

            if ($accion !== null) {
                return $accion;
            }
        }

        return ['clave' => 'ver', 'etiqueta' => 'Ver Expediente'];
    }
}
