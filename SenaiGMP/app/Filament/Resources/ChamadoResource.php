<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChamadoResource\Pages;
use App\Models\Chamado;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChamadoResource extends Resource
{
    protected static ?string $model = Chamado::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $modelLabel = 'Chamado';

    protected static ?string $pluralModelLabel = 'Chamados';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Abertura de chamado')
                    ->description('Preencha os detalhes da manutenção e defina a prioridade operacional.')
                    ->schema([
                        Select::make('user_id')
                            ->label('Responsável')
                            ->relationship('responsavel', 'name', fn (Builder $query) => $query->where('cargo', 'responsavel'))
                            ->default(fn () => auth()->user()?->cargo === 'responsavel' ? auth()->id() : null)
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('setor_id')
                            ->label('Setor solicitante')
                            ->relationship('setor', 'nome')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('patrimonio_id')
                            ->label('Patrimônio')
                            ->relationship('patrimonio', 'codigo')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('tipo')
                            ->label('Tipo de manutenção')
                            ->options(Chamado::tipoOptions())
                            ->searchable()
                            ->native(false)
                            ->required(),

                        Select::make('prioridade')
                            ->label('Nível de prioridade')
                            ->options(Chamado::prioridadeOptions())
                            ->native(false)
                            ->required(),

                        DatePicker::make('prazo')
                            ->label('Prazo')
                            ->displayFormat('d/m/Y')
                            ->native(false),

                        Select::make('status')
                            ->label('Status')
                            ->options(Chamado::statusOptions())
                            ->default(Chamado::STATUS_ABERTO)
                            ->native(false)
                            ->required()
                            ->hiddenOn('create'),

                        Textarea::make('observacao')
                            ->label('Descrição do problema')
                            ->placeholder('Descreva o problema detalhadamente...')
                            ->required()
                            ->columnSpanFull(),

                        FileUpload::make('imagem')
                            ->label('Foto do problema')
                            ->image()
                            ->directory('chamados')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                ImageColumn::make('imagem')
                    ->label('Foto')
                    ->square(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state): string => Chamado::tipoOptions()[$state] ?? 'Não informado')
                    ->searchable(),

                TextColumn::make('setor.nome')
                    ->label('Setor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('responsavel.name')
                    ->label('Responsável')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('prioridade')
                    ->label('Prioridade')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Chamado::prioridadeOptions()[$state] ?? 'Não informada')
                    ->color(fn (?string $state): string => match ($state) {
                        Chamado::PRIORIDADE_BAIXA => 'gray',
                        Chamado::PRIORIDADE_MEDIA => 'info',
                        Chamado::PRIORIDADE_ALTA => 'warning',
                        Chamado::PRIORIDADE_EMERGENCIA => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                SelectColumn::make('status')
                    ->label('Status')
                    ->options(Chamado::statusOptions())
                    ->selectablePlaceholder(false)
                    ->disabled(fn (Chamado $record): bool => auth()->user()?->cannot('update', $record) ?? true)
                    ->updateStateUsing(fn (Chamado $record, string $state): string => $record->atualizarStatusOperacional($state)),

                TextColumn::make('prazo')
                    ->label('Prazo')
                    ->date('d/m/Y')
                    ->placeholder('Sem prazo')
                    ->color(fn (Chamado $record): string => $record->isAtrasado() ? 'danger' : 'gray')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Aberto em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('observacao')
                    ->label('Descrição')
                    ->limit(48)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(Chamado::statusOptions())
                    ->native(false),

                SelectFilter::make('prioridade')
                    ->label('Prioridade')
                    ->options(Chamado::prioridadeOptions())
                    ->native(false),

                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options(Chamado::tipoOptions())
                    ->searchable()
                    ->native(false),

                SelectFilter::make('setor_id')
                    ->label('Setor')
                    ->relationship('setor', 'nome')
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('user_id')
                    ->label('Responsável')
                    ->relationship('responsavel', 'name', fn (Builder $query) => $query->where('cargo', 'responsavel'))
                    ->searchable()
                    ->preload()
                    ->native(false),

                TernaryFilter::make('atraso')
                    ->label('Prazo')
                    ->placeholder('Todos')
                    ->trueLabel('Atrasados')
                    ->falseLabel('Dentro do prazo')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->atrasados(),
                        false: fn (Builder $query): Builder => $query
                            ->ativos()
                            ->whereNotNull('prazo')
                            ->whereDate('prazo', '>=', now()->toDateString()),
                        blank: fn (Builder $query): Builder => $query,
                    ),

                Filter::make('abertura')
                    ->label('Data de abertura')
                    ->form([
                        DatePicker::make('de')
                            ->label('De')
                            ->displayFormat('d/m/Y')
                            ->native(false),
                        DatePicker::make('ate')
                            ->label('Até')
                            ->displayFormat('d/m/Y')
                            ->native(false),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['de'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
                        ->when($data['ate'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))),
            ])
            ->actions([
                Tables\Actions\Action::make('iniciar')
                    ->label('Executar')
                    ->icon('heroicon-m-play')
                    ->color('warning')
                    ->visible(fn (Chamado $record): bool => $record->podeIniciar() && (auth()->user()?->can('update', $record) ?? false))
                    ->action(function (Chamado $record): void {
                        $record->atualizarStatusOperacional(Chamado::STATUS_EM_ANDAMENTO);
                    }),

                Tables\Actions\Action::make('concluir')
                    ->label('Concluir')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->visible(fn (Chamado $record): bool => $record->podeConcluir() && (auth()->user()?->can('update', $record) ?? false))
                    ->action(function (Chamado $record): void {
                        $record->atualizarStatusOperacional(Chamado::STATUS_CONCLUIDO);
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Nenhum chamado cadastrado')
            ->emptyStateDescription('Quando houver solicitações de manutenção, elas aparecerão aqui.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChamados::route('/'),
            'create' => Pages\CreateChamado::route('/create'),
            'edit' => Pages\EditChamado::route('/{record}/edit'),
        ];
    }
}
