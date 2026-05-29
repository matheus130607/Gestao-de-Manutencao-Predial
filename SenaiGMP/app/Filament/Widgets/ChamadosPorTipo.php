<?php

namespace App\Filament\Widgets;

use App\Models\Chamado;
use Filament\Widgets\ChartWidget;

class ChamadosPorTipo extends ChartWidget
{
    protected static ?string $heading = 'Tipos mais recorrentes';

    protected static ?string $description = 'Categorias de manutenção com maior incidência';

    protected static ?int $sort = 5;

    protected static ?string $pollingInterval = '30s';

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 2,
    ];

    protected static ?string $maxHeight = '320px';

    protected static ?array $options = [
        'indexAxis' => 'y',
        'plugins' => [
            'legend' => [
                'display' => false,
            ],
        ],
        'scales' => [
            'x' => [
                'beginAtZero' => true,
                'ticks' => [
                    'precision' => 0,
                ],
            ],
        ],
        'maintainAspectRatio' => false,
    ];

    protected function getData(): array
    {
        $labels = Chamado::tipoOptions();

        $rows = Chamado::query()
            ->selectRaw("COALESCE(tipo, 'sem_tipo') as tipo, COUNT(*) as total")
            ->groupByRaw("COALESCE(tipo, 'sem_tipo')")
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'tipo');

        return [
            'datasets' => [
                [
                    'label' => 'Chamados',
                    'data' => $rows
                        ->map(fn ($total) => (int) $total)
                        ->values()
                        ->all(),
                    'backgroundColor' => '#0891b2',
                    'borderWidth' => 0,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $rows
                ->keys()
                ->map(fn (string $tipo): string => $labels[$tipo] ?? 'Não informado')
                ->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
