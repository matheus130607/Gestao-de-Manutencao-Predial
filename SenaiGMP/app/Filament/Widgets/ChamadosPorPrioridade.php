<?php

namespace App\Filament\Widgets;

use App\Models\Chamado;
use Filament\Widgets\ChartWidget;

class ChamadosPorPrioridade extends ChartWidget
{
    protected static ?string $heading = 'Prioridade dos ativos';

    protected static ?string $description = 'Chamados ainda não concluídos por nível de urgência';

    protected static ?int $sort = 4;

    protected static ?string $pollingInterval = '30s';

    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
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
        $prioridades = [
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
            'emergencia' => 'Emergência',
        ];

        $rows = Chamado::query()
            ->selectRaw('prioridade, COUNT(*) as total')
            ->where('status', '!=', 'concluido')
            ->groupBy('prioridade')
            ->pluck('total', 'prioridade');

        return [
            'datasets' => [
                [
                    'label' => 'Chamados',
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
                    'borderRadius' => 6,
                ],
            ],
            'labels' => array_values($prioridades),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
