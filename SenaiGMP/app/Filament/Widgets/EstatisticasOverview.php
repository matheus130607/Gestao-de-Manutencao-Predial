<?php

namespace App\Filament\Widgets;

use App\Models\Patrimonio;
use App\Models\Setor;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EstatisticasOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalColaboradores = User::where('cargo', 'colaborador')->count();
        $colaboradoresAtivos = User::where('cargo', 'colaborador')->where('ativo', true)->count();
        $totalPatrimonios = Patrimonio::count();
        $totalSetores = Setor::count();
        $totalResponsaveis = User::where('cargo', 'responsavel')->count();

        return [
            Stat::make('Patrimônios Cadastrados', $totalPatrimonios)
                ->description('Total de bens registrados')
                ->descriptionIcon('heroicon-o-computer-desktop')
                ->color('primary'),

            Stat::make('Colaboradores Ativos', "{$colaboradoresAtivos} / {$totalColaboradores}")
                ->description('Ativos do total cadastrado')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('Setores', $totalSetores)
                ->description('Locais cadastrados no sistema')
                ->descriptionIcon('heroicon-o-map')
                ->color('info'),

            Stat::make('Responsáveis', $totalResponsaveis)
                ->description('Responsáveis cadastrados')
                ->descriptionIcon('heroicon-o-identification')
                ->color('warning'),
        ];
    }
}
