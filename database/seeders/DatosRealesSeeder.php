<?php

namespace Database\Seeders;

use App\Models\Docente;
use App\Models\Escuela;
use App\Models\Facultad;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatosRealesSeeder extends Seeder
{
    /**
     * Datos operativos de desarrollo (Fase 0). Idempotente: el SQL ya sembró
     * 1 admin, 1 facultad y 1 escuela, así que todo usa updateOrCreate.
     */
    public function run(): void
    {
        // --- Usuarios por rol ---
        // El admin@unamba.edu.pe ya existe (semilla del SQL); aquí se asegura
        // su clave por defecto. Claves de desarrollo, cambiarlas en producción.
        Usuario::updateOrCreate(
            ['email' => 'admin@unamba.edu.pe'],
            [
                'dni' => '00000000',
                'nombres' => 'Administrador General',
                'apellidos' => 'VRIN',
                'rol_id' => 1,
                'password_hash' => Hash::make('Admin2026!'),
                'activo' => true,
            ],
        );
        Usuario::updateOrCreate(
            ['email' => 'secretaria@unamba.edu.pe'],
            [
                'dni' => '00000001',
                'nombres' => 'Secretaría',
                'apellidos' => 'VRIN',
                'rol_id' => Rol::where('nombre', 'Secretaría')->value('id') ?? 2,
                'password_hash' => Hash::make('Sgr2026!'),
                'activo' => true,
            ],
        );
        Usuario::updateOrCreate(
            ['email' => 'calidad@unamba.edu.pe'],
            [
                'dni' => '00000002',
                'nombres' => 'Unidad de Calidad',
                'apellidos' => 'VRIN',
                'rol_id' => Rol::where('nombre', 'Calidad')->value('id') ?? 3,
                'password_hash' => Hash::make('Sgr2026!'),
                'activo' => true,
            ],
        );

        // --- Estructura académica de la UNAMBA ---
        // AJUSTABLE: lista representativa para desarrollo; la estructura
        // vigente se administra luego desde el módulo de catálogos (Fase 1).
        $estructura = [
            // 'Facultad de Ciencias Agropecuarias' ya existe vía SQL con
            // 'Medicina Veterinaria y Zootecnia'; aquí solo se le suman escuelas.
            'Facultad de Ciencias Agropecuarias' => [
                'Medicina Veterinaria y Zootecnia',
                'Agronomía',
                'Zootecnia',
            ],
            'Facultad de Ingeniería' => [
                'Ingeniería de Sistemas',
                'Ingeniería Civil',
                'Ingeniería Agroindustrial',
                'Ingeniería Ambiental',
            ],
            'Facultad de Ciencias de la Salud' => [
                'Medicina Humana',
                'Enfermería',
                'Odontología',
            ],
            'Facultad de Ciencias Empresariales' => [
                'Administración',
                'Contabilidad',
                'Economía',
            ],
            'Facultad de Educación' => [
                'Educación Primaria',
                'Educación Secundaria',
                'Educación Inicial',
            ],
            'Facultad de Derecho y Ciencias Políticas' => [
                'Derecho',
                'Ciencias Políticas',
            ],
        ];

        $escuelaIds = [];
        foreach ($estructura as $nombreFacultad => $nombresEscuelas) {
            // Acrónimo por iniciales («Facultad de Ingeniería» → «FI»):
            // la columna facultades.acronimo es NOT NULL en el esquema SQL.
            $acronimo = collect(explode(' ', $nombreFacultad))
                ->reject(fn (string $palabra) => in_array(Str::lower($palabra), ['de', 'del', 'la', 'las', 'y', 'e'], true))
                ->map(fn (string $palabra) => Str::upper(Str::substr($palabra, 0, 1)))
                ->implode('');

            $facultad = Facultad::updateOrCreate(
                ['nombre' => $nombreFacultad],
                ['acronimo' => $acronimo, 'activo' => true],
            );
            foreach ($nombresEscuelas as $nombreEscuela) {
                $escuela = Escuela::updateOrCreate(
                    ['facultad_id' => $facultad->id, 'nombre' => $nombreEscuela],
                    ['activo' => true],
                );
                $escuelaIds[$nombreEscuela] = $escuela->id;
            }
        }

        // --- Docentes de prueba (DNI de 8 dígitos: ck_docentes_dni) ---
        $docentes = [
            ['dni' => '41234567', 'nombres' => 'Juan Carlos', 'apellido_paterno' => 'Quispe', 'apellido_materno' => 'Huamán', 'grado' => 'Dr.', 'tipo_contrato' => 'NOMBRADO', 'escuela' => 'Ingeniería de Sistemas'],
            ['dni' => '42345678', 'nombres' => 'María Elena', 'apellido_paterno' => 'Torres', 'apellido_materno' => 'Paredes', 'grado' => 'Dra.', 'tipo_contrato' => 'NOMBRADO', 'escuela' => 'Medicina Humana'],
            ['dni' => '43456789', 'nombres' => 'Pedro Pablo', 'apellido_paterno' => 'Cárdenas', 'apellido_materno' => null, 'grado' => 'Mg.', 'tipo_contrato' => 'CONTRATADO', 'escuela' => 'Ingeniería Civil'],
            ['dni' => '44567890', 'nombres' => 'Rosa María', 'apellido_paterno' => 'Vargas', 'apellido_materno' => 'Soto', 'grado' => 'M.Sc.', 'tipo_contrato' => 'CONTRATADO', 'escuela' => 'Medicina Veterinaria y Zootecnia'],
            ['dni' => '45678901', 'nombres' => 'Luis Alberto', 'apellido_paterno' => 'Mendoza', 'apellido_materno' => 'Ríos', 'grado' => 'Ph.D.', 'tipo_contrato' => 'NOMBRADO', 'escuela' => 'Administración'],
            ['dni' => '46789012', 'nombres' => 'Carmen Rosa', 'apellido_paterno' => 'Flores', 'apellido_materno' => 'Ccama', 'grado' => 'Mg.', 'tipo_contrato' => 'CONTRATADO', 'escuela' => 'Educación Primaria'],
            ['dni' => '47890123', 'nombres' => 'Jorge Luis', 'apellido_paterno' => 'Ramírez', 'apellido_materno' => 'Palomino', 'grado' => 'Ing.', 'tipo_contrato' => 'CONTRATADO', 'escuela' => 'Ingeniería Agroindustrial'],
            ['dni' => '48901234', 'nombres' => 'Ana Lucía', 'apellido_paterno' => 'Gutiérrez', 'apellido_materno' => 'Vega', 'grado' => 'Abog.', 'tipo_contrato' => 'NOMBRADO', 'escuela' => 'Derecho'],
        ];

        foreach ($docentes as $datos) {
            Docente::updateOrCreate(
                ['dni' => $datos['dni']],
                [
                    'nombres' => $datos['nombres'],
                    'apellido_paterno' => $datos['apellido_paterno'],
                    'apellido_materno' => $datos['apellido_materno'],
                    'grado' => $datos['grado'],
                    'tipo_contrato' => $datos['tipo_contrato'],
                    'escuela_id' => $escuelaIds[$datos['escuela']],
                    'activo' => true,
                    'origen' => 'LOCAL',
                ],
            );
        }
    }
}
