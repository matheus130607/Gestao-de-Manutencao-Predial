<?php

namespace App\Filament\Widgets;

use App\Models\Chamado;
use Filament\Widgets\ChartWidget;

class ChamadosPorSetor extends ChartWidget
{
    protected static ?string $heading = 'Chamados por setor';

    protected static ?string $description = 'Distribuição das demandas por área solicitante';

    protected static ?int $sort = 3;

    protected static ?string $pollingInterval = '30s';

    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
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
        $rows = Chamado::query()
            ->leftJoin('setors', 'chamados.setor_id', '=', 'setors.id')
            ->selectRaw("COALESCE(setors.nome, 'Sem setor') as setor, COUNT(*) as total")
            ->groupByRaw("COALESCE(setors.nome, 'Sem setor')")
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        if ($rows->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'data' => [1],
                        'backgroundColor' => ['#e5e7eb'],
                        'borderWidth' => 0,
                    ],
                ],
                'labels' => ['Sem chamados'],
            ];
        }

        return [
            'datasets' => [
                [
                    'data' => $rows->pluck('total')->map(fn ($total) => (int) $total)->all(),
                    'backgroundColor' => [
                        '#dc2626',
                        '#f59e0b',
                        '#2563eb',
                        '#16a34a',
                        '#7c3aed',
                        '#0891b2',
                        '#475569',
                        '#db2777',
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $rows->pluck('setor')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
