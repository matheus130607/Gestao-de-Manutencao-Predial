<?php

namespace App\Filament\Widgets;

use App\Models\Chamado;
use Filament\Widgets\ChartWidget;

class ChamadosEvolucao extends ChartWidget
{
    protected static ?string $heading = 'Evolução operacional';

    protected static ?string $description = 'Aberturas, início de execução e conclusões no período selecionado';

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = '30s';

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 2,
        'xl' => 4,
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

        $abertos = Chamado::query()
            ->visibleTo(auth()->user())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->whereDate('created_at', '>=', $start->toDateString())
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'date');

        $iniciados = Chamado::query()
            ->visibleTo(auth()->user())
            ->selectRaw('DATE(iniciado_em) as date, COUNT(*) as total')
            ->whereNotNull('iniciado_em')
            ->whereDate('iniciado_em', '>=', $start->toDateString())
            ->groupByRaw('DATE(iniciado_em)')
            ->pluck('total', 'date');

        $concluidos = Chamado::query()
            ->visibleTo(auth()->user())
            ->selectRaw('DATE(COALESCE(concluido_em, updated_at)) as date, COUNT(*) as total')
            ->where('status', Chamado::STATUS_CONCLUIDO)
            ->where(function ($query) use ($start): void {
                $query
                    ->whereDate('concluido_em', '>=', $start->toDateString())
                    ->orWhere(function ($query) use ($start): void {
                        $query
                            ->whereNull('concluido_em')
                            ->whereDate('updated_at', '>=', $start->toDateString());
                    });
            })
            ->groupByRaw('DATE(COALESCE(concluido_em, updated_at))')
            ->pluck('total', 'date');

        return [
            'datasets' => [
                [
                    'label' => 'Abertos',
                    'data' => $this->seriesFor($period, $abertos),
                    'borderColor' => '#dc2626',
                    'backgroundColor' => 'rgba(220, 38, 38, .10)',
                    'tension' => .35,
                    'fill' => true,
                    'pointRadius' => 2,
                ],
                [
                    'label' => 'Iniciados',
                    'data' => $this->seriesFor($period, $iniciados),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, .10)',
                    'tension' => .35,
                    'fill' => true,
                    'pointRadius' => 2,
                ],
                [
                    'label' => 'Concluídos',
                    'data' => $this->seriesFor($period, $concluidos),
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, .10)',
                    'tension' => .35,
                    'fill' => true,
                    'pointRadius' => 2,
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

    private function seriesFor($period, $rows): array
    {
        return $period
            ->map(fn ($date): int => (int) ($rows[$date->toDateString()] ?? 0))
            ->all();
    }
}
