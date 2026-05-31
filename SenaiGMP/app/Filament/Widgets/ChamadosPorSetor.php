<?php

namespace App\Filament\Widgets;

use App\Models\Chamado;
use Filament\Widgets\ChartWidget;

class ChamadosPorSetor extends ChartWidget
{
    protected static ?string $heading = 'Demandas por setor';

    protected static ?string $description = 'Setores solicitantes com maior volume de chamados';

    protected static ?int $sort = 4;

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
        $rows = Chamado::query()
            ->leftJoin('setors', 'chamados.setor_id', '=', 'setors.id')
            ->selectRaw("COALESCE(setors.nome, 'Sem setor') as setor, COUNT(*) as total")
            ->groupByRaw("COALESCE(setors.nome, 'Sem setor')")
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Chamados',
                    'data' => $rows->pluck('total')->map(fn ($total) => (int) $total)->all(),
                    'backgroundColor' => '#2563eb',
                    'borderWidth' => 0,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $rows->pluck('setor')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
