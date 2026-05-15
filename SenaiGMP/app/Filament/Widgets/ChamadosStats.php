<?php

namespace App\Filament\Widgets;

use App\Models\Chamado;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ChamadosStats extends BaseWidget
{
    // Atualiza os dados automaticamente a cada 10 segundos
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        return [
            Stat::make('Chamados em Aberto', Chamado::where('status', 'aberto')->count())
                ->description('Aguardando um técnico')
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color('danger') // Vermelho
                ->chart([7, 3, 4, 5, 6, 3, 5, 2]), // Gráfico de linha decorativo

            Stat::make('Em Andamento', Chamado::where('status', 'em_andamento')->count())
                ->description('Manutenções iniciadas')
                ->descriptionIcon('heroicon-m-wrench')
                ->color('warning') // Amarelo/Laranja
                ->chart([2, 4, 6, 4, 7, 5, 8, 4]),

            Stat::make('Concluídos', Chamado::where('status', 'concluido')->count())
                ->description('Finalizados com sucesso')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success') // Verde
                ->chart([1, 3, 6, 4, 8, 5, 5, 7]),
        ];
    }
}