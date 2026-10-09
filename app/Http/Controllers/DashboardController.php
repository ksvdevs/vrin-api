<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Expediente;
use App\Models\Plantilla;
use App\Models\Rendicion;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): array
    {
        $this->authorize('viewAny', Expediente::class);
        $year = $request->validate(['year' => ['nullable', 'integer', 'between:1900,2100']])['year'] ?? now()->year;

        $estados = Expediente::query()
            ->whereYear('created_at', $year)
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'year' => $year,
            'expedientes' => Expediente::count(),
            'plantillas' => Plantilla::count(),
            'plantillas_actualizadas' => Plantilla::whereYear('updated_at', $year)->count(),
            'docentes' => Docente::count(),
            'docentes_activos' => Docente::where('activo', true)->count(),
            'monto_financiado' => (float) Rendicion::whereYear('fecha_desembolso', $year)->sum('monto_desembolsado'),
            'estados' => $estados,
            'actividad' => Expediente::with('docente')
                ->orderByDesc('updated_at')
                ->limit(3)
                ->get()
                ->map(fn (Expediente $expediente) => [
                    'id' => $expediente->id,
                    'codigo' => $expediente->codigo,
                    'estado' => $expediente->estado,
                    'docente' => trim(implode(' ', array_filter([
                        $expediente->docente?->nombres,
                        $expediente->docente?->apellido_paterno,
                        $expediente->docente?->apellido_materno,
                    ]))),
                    'updated_at' => $expediente->updated_at?->toIso8601String(),
                ]),
        ];
    }

    public function reporte(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Expediente::class);
        $year = $request->validate(['year' => ['nullable', 'integer', 'between:1900,2100']])['year'] ?? now()->year;

        return response()->streamDownload(function () use ($year): void {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Código', 'Fecha de registro', 'Docente', 'Estado', 'Monto solicitado', 'Monto desembolsado'], ';');

            Expediente::with(['docente', 'articulo', 'rendicion'])
                ->whereYear('created_at', $year)
                ->orderBy('id')
                ->chunk(200, function ($expedientes) use ($salida): void {
                    foreach ($expedientes as $expediente) {
                        fputcsv($salida, [
                            $expediente->codigo,
                            $expediente->created_at?->format('Y-m-d'),
                            trim(implode(' ', array_filter([
                                $expediente->docente?->nombres,
                                $expediente->docente?->apellido_paterno,
                                $expediente->docente?->apellido_materno,
                            ]))),
                            $expediente->estado,
                            $expediente->articulo?->monto_solicitado ?? 0,
                            $expediente->rendicion?->monto_desembolsado ?? 0,
                        ], ';');
                    }
                });
            fclose($salida);
        }, "reporte-expedientes-$year.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
