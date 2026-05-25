<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ChamadoResource;
use App\Models\Chamado;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ChamadosRecentes extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Chamados recentes')
            ->description('Últimas solicitações abertas e situação atual')
            ->query($this->getTableQuery())
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state): string => $this->formatTipo($state))
                    ->placeholder('Não informado'),

                Tables\Columns\TextColumn::make('setor.nome')
                    ->label('Setor')
                    ->searchable()
                    ->placeholder('Sem setor'),

                Tables\Columns\TextColumn::make('responsavel.name')
                    ->label('Responsável')
                    ->placeholder('Não atribuído'),

                Tables\Columns\TextColumn::make('prioridade')
                    ->label('Prioridade')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $this->formatPrioridade($state))
                    ->color(fn (?string $state): string => match ($state) {
                        'baixa' => 'gray',
                        'media' => 'info',
                        'alta' => 'warning',
                        'emergencia' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $this->formatStatus($state))
                    ->color(fn (?string $state): string => match ($state) {
                        'aberto' => 'danger',
                        'em_andamento' => 'warning',
                        'concluido' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Aberto')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('abrir')
                    ->label('Abrir')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Chamado $record): string => ChamadoResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('Nenhum chamado cadastrado')
            ->emptyStateDescription('Quando houver solicitações, elas aparecerão aqui.');
    }

    protected function getTableQuery(): Builder
    {
        return Chamado::query()
            ->with(['setor', 'responsavel'])
            ->latest();
    }

    private function formatTipo(?string $state): string
    {
        return match ($state) {
            'hidraulica' => 'Hidráulica',
            'eletrica' => 'Elétrica',
            'alvenaria' => 'Alvenaria',
            'pintura' => 'Pintura',
            'ar_condicionado' => 'Ar condicionado',
            'marcenaria' => 'Marcenaria',
            'serralheria' => 'Serralheria',
            default => 'Não informado',
        };
    }

    private function formatPrioridade(?string $state): string
    {
        return match ($state) {
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
            'emergencia' => 'Emergência',
            default => 'Não informada',
        };
    }

    private function formatStatus(?string $state): string
    {
        return match ($state) {
            'aberto' => 'Aberto',
            'em_andamento' => 'Em andamento',
            'concluido' => 'Concluído',
            default => 'Não informado',
        };
    }
}
