<?php

namespace App\Support;

/**
 * Mapa declarativo de los 11 estados congelados del expediente (D-05):
 * etapa de presentación (§1.1 del plan), badge de lista y terminalidad.
 */
final class EstadoExpediente
{
    /**
     * @var array<string, array{etapa: int|null, label: string, severity: string, terminal: bool}>
     */
    private const MAPA = [
        'OBSERVADO' => ['etapa' => 1, 'label' => 'Observado', 'severity' => 'danger', 'terminal' => false],
        'EN_REVISION_CALIDAD' => ['etapa' => 1, 'label' => 'En revisión', 'severity' => 'warn', 'terminal' => false],
        'VALIDADO_CALIDAD' => ['etapa' => 1, 'label' => 'Validado', 'severity' => 'success', 'terminal' => false],
        'NO_CUMPLE' => ['etapa' => null, 'label' => 'No cumple', 'severity' => 'danger', 'terminal' => true],
        'EN_ESPERA_OPP' => ['etapa' => 2, 'label' => 'En espera OPP', 'severity' => 'warn', 'terminal' => false],
        'SIN_DISPONIBILIDAD' => ['etapa' => 2, 'label' => 'Sin disponibilidad', 'severity' => 'danger', 'terminal' => true],
        'DISPONIBILIDAD_CONFIRMADA' => ['etapa' => 2, 'label' => 'Disponibilidad OK', 'severity' => 'success', 'terminal' => false],
        'RESOLUCION_EMITIDA' => ['etapa' => 3, 'label' => 'Emitida', 'severity' => 'success', 'terminal' => false],
        'POR_RENDIR' => ['etapa' => 4, 'label' => 'Por rendir', 'severity' => 'danger', 'terminal' => false],
        'RENDICION_VENCIDA' => ['etapa' => 4, 'label' => 'Vencida', 'severity' => 'danger', 'terminal' => false],
        'RENDIDO' => ['etapa' => 4, 'label' => 'Rendido', 'severity' => 'success', 'terminal' => true],
    ];

    /**
     * @return array<int, string>
     */
    public static function todos(): array
    {
        return array_keys(self::MAPA);
    }

    public static function etapa(string $estado): ?int
    {
        return self::MAPA[$estado]['etapa'] ?? null;
    }

    /**
     * @return array{label: string, severity: string}
     */
    public static function badge(string $estado): array
    {
        $fila = self::MAPA[$estado] ?? self::MAPA['OBSERVADO'];

        return [
            'label' => $fila['label'],
            'severity' => $fila['severity'],
        ];
    }

    public static function esTerminal(string $estado): bool
    {
        return self::MAPA[$estado]['terminal'] ?? false;
    }
}
