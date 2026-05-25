<?php

namespace App\Filament\Widgets;

use App\Models\Chamado;
use Filament\Widgets\ChartWidget;

class ChamadosEvolucao extends ChartWidget
{
    protected static ?string $heading = 'Evolução dos chamados';

    protected static ?string $description = 'Abertos, em execução e concluídos ao longo do período';

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = '30s';

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    protected static ?string $maxHeight = '320px';

    protected static ?array $options = [
        'interaction' => [
            'intersect' => false,
            'mode' => 'index',
        ],
        'plugins' => [
            'legend' => [
                'position' => 'bottom',
            ],
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'ticks' => [
                    'precision' => 0,
                ],
            ],
        ],
        'maintainAspectRatio' => false,
    ];

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7' => '7 dias',
            '30' => '30 dias',
            '90' => '90 dias',
        ];
    }

    protected function getData(): array
    {
        $days = (int) ($this->filter ?: 30);
        $start = now()->subDays($days - 1)->startOfDay();

        $period = collect(range(0, $days - 1))
            ->map(fn (int $day) => $start->copy()->addDays($day));

        $rows = Chamado::query()
            ->selectRaw('DATE(created_at) as date, status, COUNT(*) as total')
            ->whereDate('created_at', '>=', $start->toDateString())
            ->groupByRaw('DATE(created_at), status')
            ->get()
            ->keyBy(fn ($row) => "{$row->date}|{$row->status}");

        return [
            'datasets' => [
                [
                    'label' => 'Abertos',
                    'data' => $this->seriesFor($period, $rows, 'aberto'),
                    'borderColor' => '#dc2626',
                    'backgroundColor' => 'rgba(220, 38, 38, .12)',
                    'tension' => .35,
                    'fill' => true,
                ],
                [
                    'label' => 'Em andamento',
                    'data' => $this->seriesFor($period, $rows, 'em_andamento'),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, .12)',
                    'tension' => .35,
                    'fill' => true,
                ],
                [
                    'label' => 'Concluídos',
                    'data' => $this->seriesFor($period, $rows, 'concluido'),
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, .12)',
                    'tension' => .35,
                    'fill' => true,
                ],
            ],
            'labels' => $period
                ->map(fn ($date) => $date->format('d/m'))
                ->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function seriesFor($period, $rows, string $status): array
    {
        return $period
            ->map(function ($date) use ($rows, $status): int {
                $key = "{$date->toDateString()}|{$status}";

                return (int) ($rows->get($key)->total ?? 0);
            })
            ->all();
    }
}
