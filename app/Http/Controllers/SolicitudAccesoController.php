<?php

namespace App\Http\Controllers;

use App\Models\SolicitudAcceso;
use App\Models\Usuario;
use Illuminate\Http\Request;

class SolicitudAccesoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'dni' => 'required|regex:/^[0-9]{8}$/',
            'email' => 'required|email|max:150|ends_with:@unamba.edu.pe',
            'dependencia' => 'required|string|max:150',
            'cargo' => 'required|string|max:150',
            'motivo' => 'required|string',
        ]);

        if (Usuario::where('email', $data['email'])->exists()) {
            return response()->json(['message' => 'Este correo ya tiene acceso.'], 409);
        }

        if (SolicitudAcceso::where('email', $data['email'])->where('estado', 'PENDIENTE')->exists()) {
            return response()->json(['message' => 'Ya existe una solicitud pendiente.'], 409);
        }

        $solicitud = SolicitudAcceso::create(array_merge($data, [
            'estado' => 'PENDIENTE',
            'fecha_solicitud' => now(),
        ]));

        return response()->json($solicitud, 201);
    }
}
