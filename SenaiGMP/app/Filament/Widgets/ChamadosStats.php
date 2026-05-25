<?php

namespace App\Filament\Widgets;

use App\Models\Chamado;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ChamadosStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $abertos = Chamado::query()->where('status', 'aberto')->count();
        $andamento = Chamado::query()->where('status', 'em_andamento')->count();
        $concluidosMes = Chamado::query()
            ->where('status', 'concluido')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();
        $criticos = Chamado::query()
            ->whereIn('prioridade', ['alta', 'emergencia'])
            ->where('status', '!=', 'concluido')
            ->count();

        return [
            Stat::make('Chamados abertos', $abertos)
                ->description('Aguardando primeira ação')
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color($abertos > 0 ? 'danger' : 'success')
                ->chart($this->dailyCounts(status: 'aberto')),

            Stat::make('Em andamento', $andamento)
                ->description('Execução em curso')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('warning')
                ->chart($this->dailyCounts(status: 'em_andamento')),

            Stat::make('Concluídos no mês', $concluidosMes)
                ->description('Demandas finalizadas')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->chart($this->dailyCounts(status: 'concluido')),

            Stat::make('Críticos ativos', $criticos)
                ->description('Alta prioridade ou emergência')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($criticos > 0 ? 'danger' : 'gray')
                ->chart($this->dailyCounts(prioridades: ['alta', 'emergencia'], onlyActive: true)),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function dailyCounts(?string $status = null, array $prioridades = [], bool $onlyActive = false): array
    {
        $start = now()->subDays(6)->startOfDay();

        return collect(range(0, 6))
            ->map(function (int $day) use ($start, $status, $prioridades, $onlyActive): int {
                $date = $start->copy()->addDays($day)->toDateString();

                return Chamado::query()
                    ->when($status, fn ($query) => $query->where('status', $status))
                    ->when($prioridades !== [], fn ($query) => $query->whereIn('prioridade', $prioridades))
                    ->when($onlyActive, fn ($query) => $query->where('status', '!=', 'concluido'))
                    ->whereDate('created_at', $date)
                    ->count();
            })
            ->all();
    }
}
