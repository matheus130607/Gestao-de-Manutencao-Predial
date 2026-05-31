<?php

namespace App\Filament\Widgets;

use App\Models\Chamado;
use Filament\Widgets\ChartWidget;

class ChamadosPorPrioridade extends ChartWidget
{
    protected static ?string $heading = 'Prioridade dos ativos';

    protected static ?string $description = 'Distribuição dos chamados ainda não finalizados';

    protected static ?int $sort = 3;

    protected static ?string $pollingInterval = '30s';

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 2,
    ];

    protected static ?string $maxHeight = '320px';

    protected static ?array $options = [
        'plugins' => [
            'legend' => [
                'position' => 'bottom',
            ],
        ],
        'maintainAspectRatio' => false,
        'cutout' => '62%',
    ];

    protected function getData(): array
    {
        $prioridades = Chamado::prioridadeOptions();

        $rows = Chamado::query()
            ->ativos()
            ->selectRaw('prioridade, COUNT(*) as total')
            ->groupBy('prioridade')
            ->pluck('total', 'prioridade');

        return [
            'datasets' => [
                [
                    'data' => collect(array_keys($prioridades))
                        ->map(fn (string $prioridade) => (int) ($rows[$prioridade] ?? 0))
                        ->all(),
                    'backgroundColor' => [
                        '#64748b',
                        '#2563eb',
                        '#f59e0b',
                        '#dc2626',
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => array_values($prioridades),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
