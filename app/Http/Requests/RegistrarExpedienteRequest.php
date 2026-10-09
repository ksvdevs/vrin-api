<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrarExpedienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización la maneja ExpedientePolicy en el controlador.
        return true;
    }

    public function rules(): array
    {
        return [
            'carta_docente_numero' => ['required', 'string', 'max:80'], // sin el prefijo «CARTA N°»
            'carta_docente_registro_numero' => ['required', 'string', 'max:80'],
            'carta_docente_registro_fecha' => ['required', 'date'],
            'carta_docente_fecha' => ['required', 'date'],
            'docente_id' => ['required', 'integer', Rule::exists('docentes', 'id')],
            // grado, tipo_contrato y escuela_id NO se piden: se toman del docente (snapshot)
            'facultad_id' => ['required', 'integer', Rule::exists('facultades', 'id')],
            'titulo' => ['required', 'string'],
            'revista' => ['required', 'string', 'max:255'],
            'base_indexadora' => ['required', Rule::in(['Scopus', 'Web of Science', 'SciELO', 'Otra'])],
            'cuartil' => ['required', Rule::in(['Q1', 'Q2', 'Q3', 'Q4'])],
            'monto_solicitado' => ['required', 'numeric', 'min:0.01'], // ck_art_monto
            'doi' => ['nullable', 'string', 'max:255'], // RN-07: no obligatorio
            'documentos_completos' => ['required', 'boolean'], // RN-12
            'confirmar_duplicado' => ['sometimes', 'boolean'], // D-17: duplicidad blanda
        ];
    }

    public function messages(): array
    {
        return [
            'carta_docente_numero.required' => 'El número de la carta del docente es obligatorio.',
            'carta_docente_numero.max' => 'El número de la carta no puede superar los 80 caracteres.',
            'carta_docente_registro_numero.required' => 'El registro de la carta del docente es obligatorio.',
            'carta_docente_registro_numero.max' => 'El registro de la carta no puede superar los 80 caracteres.',
            'carta_docente_registro_fecha.required' => 'La fecha de registro de la carta del docente es obligatoria.',
            'carta_docente_registro_fecha.date' => 'La fecha de registro de la carta del docente no es válida.',
            'carta_docente_fecha.required' => 'La fecha de la carta del docente es obligatoria.',
            'carta_docente_fecha.date' => 'La fecha de la carta del docente no es válida.',
            'docente_id.required' => 'El docente es obligatorio.',
            'docente_id.exists' => 'El docente seleccionado no existe.',
            'facultad_id.required' => 'La facultad es obligatoria.',
            'facultad_id.exists' => 'La facultad seleccionada no existe.',
            'titulo.required' => 'El título del artículo es obligatorio.',
            'revista.required' => 'La revista es obligatoria.',
            'revista.max' => 'La revista no puede superar los 255 caracteres.',
            'base_indexadora.required' => 'La base indexadora es obligatoria.',
            'base_indexadora.in' => 'La base indexadora debe ser Scopus, Web of Science, SciELO u Otra.',
            'cuartil.required' => 'El cuartil es obligatorio.',
            'cuartil.in' => 'El cuartil debe ser Q1, Q2, Q3 o Q4.',
            'monto_solicitado.required' => 'El monto solicitado es obligatorio.',
            'monto_solicitado.numeric' => 'El monto solicitado debe ser un número.',
            'monto_solicitado.min' => 'El monto solicitado debe ser mayor que cero.',
            'doi.max' => 'El DOI no puede superar los 255 caracteres.',
            'documentos_completos.required' => 'Debe indicar si los documentos están completos.',
            'documentos_completos.boolean' => 'El campo documentos completos debe ser verdadero o falso.',
            'confirmar_duplicado.boolean' => 'El campo confirmar duplicado debe ser verdadero o falso.',
        ];
    }
}
