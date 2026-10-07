<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Espejo de ck_opp_completa / ck_opp_meta (RN-06): si la disponibilidad es
 * «SI» exige monto, meta (3 dígitos), específica y fuente; si es «NO» esos
 * campos deben venir vacíos.
 */
class RespuestaOppRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización la maneja ExpedientePolicy en el controlador.
        return true;
    }

    public function rules(): array
    {
        return [
            'disponibilidad' => ['required', 'in:SI,NO'],
            'carta_numero' => ['required', 'string', 'max:80'],
            'carta_fecha' => ['required', 'date'],
            'monto_aprobado' => [
                'prohibited_if:disponibilidad,NO',
                'required_if:disponibilidad,SI',
                'numeric',
                'min:0.01',
            ],
            'meta_presupuestal' => [
                'prohibited_if:disponibilidad,NO',
                'required_if:disponibilidad,SI',
                'string',
                'regex:/^[0-9]{3}$/',
            ],
            'especifica_gasto' => [
                'prohibited_if:disponibilidad,NO',
                'required_if:disponibilidad,SI',
                'string',
                'max:30',
            ],
            'fuente_financiamiento' => [
                'prohibited_if:disponibilidad,NO',
                'required_if:disponibilidad,SI',
                'string',
                'max:100',
            ],
            'registro_vrin_numero' => ['required', 'string', 'max:30'],
            'registro_vrin_fecha' => ['required', 'date'],
            'resolucion_numero' => ['nullable', 'integer', 'min:1'],
            'resolucion_anio' => ['nullable', 'integer', 'between:2020,2100'],
            'resolucion_fecha_emision' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'disponibilidad.required' => 'Debe indicar la disponibilidad presupuestal.',
            'disponibilidad.in' => 'La disponibilidad debe ser SI o NO.',
            'carta_numero.required' => 'El número de carta OPP es obligatorio.',
            'carta_fecha.required' => 'La fecha de carta OPP es obligatoria.',
            'monto_aprobado.required_if' => 'El monto aprobado es obligatorio cuando hay disponibilidad.',
            'monto_aprobado.min' => 'El monto aprobado debe ser mayor que cero.',
            'monto_aprobado.prohibited_if' => 'Si no hay disponibilidad, el monto aprobado debe ir vacío.',
            'meta_presupuestal.required_if' => 'La meta presupuestal es obligatoria cuando hay disponibilidad.',
            'meta_presupuestal.regex' => 'La meta presupuestal debe tener exactamente 3 dígitos.',
            'meta_presupuestal.prohibited_if' => 'Si no hay disponibilidad, la meta presupuestal debe ir vacía.',
            'especifica_gasto.required_if' => 'La específica de gasto es obligatoria cuando hay disponibilidad.',
            'especifica_gasto.prohibited_if' => 'Si no hay disponibilidad, la específica de gasto debe ir vacía.',
            'fuente_financiamiento.required_if' => 'La fuente de financiamiento es obligatoria cuando hay disponibilidad.',
            'fuente_financiamiento.prohibited_if' => 'Si no hay disponibilidad, la fuente de financiamiento debe ir vacía.',
            'registro_vrin_numero.required' => 'El número de registro VRIN es obligatorio.',
            'registro_vrin_fecha.required' => 'La fecha de registro VRIN es obligatoria.',
        ];
    }
}
