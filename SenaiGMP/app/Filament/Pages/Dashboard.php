<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ChamadoResource;
use App\Filament\Widgets\ChamadosEvolucao;
use App\Filament\Widgets\ChamadosPorPrioridade;
use App\Filament\Widgets\ChamadosPorSetor;
use App\Filament\Widgets\ChamadosRecentes;
use App\Filament\Widgets\ChamadosStats;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard operacional';

    protected static ?int $navigationSort = -2;

    protected ?string $maxContentWidth = 'full';

    public function getHeading(): string
    {
        return 'Visão geral da manutenção predial';
    }

    public function getSubheading(): ?string
    {
        return 'Indicadores atualizados dos chamados, prioridades e áreas com maior demanda.';
    }

    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 2,
        ];
    }

    public function getWidgets(): array
    {
        return [
            ChamadosStats::class,
            ChamadosEvolucao::class,
            ChamadosPorSetor::class,
            ChamadosPorPrioridade::class,
            ChamadosRecentes::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('novoChamado')
                ->label('Novo chamado')
                ->icon('heroicon-m-plus')
                ->url(ChamadoResource::getUrl('create')),

            Action::make('verChamados')
                ->label('Ver chamados')
                ->icon('heroicon-m-list-bullet')
                ->color('gray')
                ->url(ChamadoResource::getUrl('index')),
        ];
    }
}
