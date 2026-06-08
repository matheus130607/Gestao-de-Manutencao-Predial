<?php

namespace App\Filament\Widgets;

use App\Models\Chamado;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ChamadosStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '30s';

    protected ?string $heading = 'Indicadores operacionais';

    protected ?string $description = 'Resumo do volume, urgência, prazos e desempenho de conclusão.';

    protected function getStats(): array
    {
        $abertos = $this->visibleChamadosQuery()->where('status', Chamado::STATUS_ABERTO)->count();
        $andamento = $this->visibleChamadosQuery()->where('status', Chamado::STATUS_EM_ANDAMENTO)->count();
        $atrasados = $this->visibleChamadosQuery()->atrasados()->count();
        $criticos = $this->visibleChamadosQuery()
            ->ativos()
            ->whereIn('prioridade', Chamado::prioridadesCriticas())
            ->count();
        $concluidosMes = $this->visibleChamadosQuery()
            ->where('status', Chamado::STATUS_CONCLUIDO)
            ->where(function ($query): void {
                $query
                    ->whereBetween('concluido_em', [now()->startOfMonth(), now()->endOfMonth()])
                    ->orWhere(function ($query): void {
                        $query
                            ->whereNull('concluido_em')
                            ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    });
            })
            ->count();

        return [
            Stat::make('Chamados abertos', $abertos)
                ->description('Aguardando primeira ação')
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color($abertos > 0 ? 'danger' : 'success')
                ->chart($this->dailyCounts(status: Chamado::STATUS_ABERTO)),

            Stat::make('Em andamento', $andamento)
                ->description('Execução em curso')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('warning')
                ->chart($this->dailyCounts(status: Chamado::STATUS_EM_ANDAMENTO)),

            Stat::make('Concluídos no mês', $concluidosMes)
                ->description('Demandas finalizadas')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->chart($this->dailyCounts(status: Chamado::STATUS_CONCLUIDO)),

            Stat::make('Críticos ativos', $criticos)
                ->description('Alta prioridade ou emergência')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($criticos > 0 ? 'danger' : 'gray')
                ->chart($this->dailyCounts(prioridades: Chamado::prioridadesCriticas(), onlyActive: true)),

            Stat::make('Atrasados', $atrasados)
                ->description('Ativos fora do prazo')
                ->descriptionIcon('heroicon-m-clock')
                ->color($atrasados > 0 ? 'danger' : 'success')
                ->chart($this->dailyCounts(onlyOverdue: true)),

            Stat::make('Tempo médio', $this->averageCompletionLabel())
                ->description('Conclusão dos últimos 90 dias')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('info'),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function dailyCounts(
        ?string $status = null,
        array $prioridades = [],
        bool $onlyActive = false,
        bool $onlyOverdue = false,
    ): array {
        $start = now()->subDays(6)->startOfDay();

        return collect(range(0, 6))
            ->map(function (int $day) use ($start, $status, $prioridades, $onlyActive, $onlyOverdue): int {
                $date = $start->copy()->addDays($day)->toDateString();

                return $this->visibleChamadosQuery()
                    ->when($status, fn ($query) => $query->where('status', $status))
                    ->when($prioridades !== [], fn ($query) => $query->whereIn('prioridade', $prioridades))
                    ->when($onlyActive, fn ($query) => $query->ativos())
                    ->when($onlyOverdue, fn ($query) => $query->atrasados())
                    ->whereDate('created_at', $date)
                    ->count();
            })
            ->all();
    }

    private function averageCompletionLabel(): string
    {
        $durations = $this->visibleChamadosQuery()
            ->where('status', Chamado::STATUS_CONCLUIDO)
            ->whereDate('created_at', '>=', now()->subDays(90)->toDateString())
            ->get(['created_at', 'updated_at', 'concluido_em'])
            ->map(function (Chamado $chamado): ?float {
                $finishedAt = $chamado->concluido_em ?? $chamado->updated_at;

                if (! $finishedAt || ! $chamado->created_at) {
                    return null;
                }

                return $chamado->created_at->diffInMinutes($finishedAt) / 60;
            })
            ->filter();

        if ($durations->isEmpty()) {
            return 'Sem dados';
        }

        $hours = (float) $durations->avg();

        if ($hours >= 24) {
            return number_format($hours / 24, 1, ',', '.') . ' dias';
        }

        return number_format($hours, 1, ',', '.') . ' h';
    }

    private function visibleChamadosQuery()
    {
        return Chamado::query()->visibleTo(auth()->user());
    }
}
