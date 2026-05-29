<?php

namespace App\Filament\Widgets;

use App\Models\Chamado;
use Filament\Widgets\ChartWidget;

class ChamadosPorResponsavel extends ChartWidget
{
    protected static ?string $heading = 'Carga por responsável';

    protected static ?string $description = 'Chamados ativos distribuídos entre responsáveis';

    protected static ?int $sort = 6;

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
            ->ativos()
            ->leftJoin('users', 'chamados.user_id', '=', 'users.id')
            ->selectRaw("COALESCE(users.name, 'Não atribuído') as responsavel, COUNT(*) as total")
            ->groupByRaw("COALESCE(users.name, 'Não atribuído')")
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Chamados ativos',
                    'data' => $rows->pluck('total')->map(fn ($total) => (int) $total)->all(),
                    'backgroundColor' => '#7c3aed',
                    'borderWidth' => 0,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $rows->pluck('responsavel')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
