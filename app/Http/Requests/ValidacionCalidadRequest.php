<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ValidacionCalidadRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('motivo_correccion'))) {
            $this->merge(['motivo_correccion' => trim($this->input('motivo_correccion'))]);
        }
    }

    public function authorize(): bool
    {
        // La autorización la maneja ExpedientePolicy en el controlador.
        return true;
    }

    public function rules(): array
    {
        $esCorreccion = in_array($this->route('expediente')?->estado, ['VALIDADO_CALIDAD', 'NO_CUMPLE'], true);

        return [
            'resultado' => ['required', 'in:CUMPLE,NO_CUMPLE'],
            'checklist' => ['required', 'array'],
            'checklist.carta_aceptacion' => ['required', 'boolean'],
            'checklist.docente_ordinario_contratado' => ['required', 'boolean'],
            'checklist.afiliacion_universidad' => ['required', 'boolean'],
            'observacion' => ['nullable', 'string'],
            'motivo_correccion' => [$esCorreccion ? 'required' : 'nullable', 'string', 'min:10', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->any()) {
                return;
            }

            $checklist = $this->input('checklist');
            $todosCumplen = (bool) $checklist['carta_aceptacion']
                && (bool) $checklist['docente_ordinario_contratado']
                && (bool) $checklist['afiliacion_universidad'];
            if (($this->input('resultado') === 'CUMPLE') !== $todosCumplen) {
                $validator->errors()->add('resultado', 'El resultado debe coincidir con los requisitos verificados.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'motivo_correccion.required' => 'Indica por qué necesitas corregir la evaluación.',
            'motivo_correccion.min' => 'El motivo debe tener al menos 10 caracteres.',
            'resultado.required' => 'El resultado de la validación es obligatorio.',
            'resultado.in' => 'El resultado debe ser CUMPLE o NO_CUMPLE.',
            'checklist.required' => 'El checklist de requisitos es obligatorio.',
            'checklist.array' => 'El checklist de requisitos no es válido.',
            'checklist.carta_aceptacion.required' => 'Debe indicar la Carta Oficial de Aceptación.',
            'checklist.carta_aceptacion.boolean' => 'El valor de la Carta Oficial de Aceptación no es válido.',
            'checklist.docente_ordinario_contratado.required' => 'Debe indicar Docente Ordinario o Contratado.',
            'checklist.docente_ordinario_contratado.boolean' => 'El valor de Docente Ordinario/Contratado no es válido.',
            'checklist.afiliacion_universidad.required' => 'Debe indicar la Afiliación a la Universidad.',
            'checklist.afiliacion_universidad.boolean' => 'El valor de Afiliación a la Universidad no es válido.',
        ];
    }
}
