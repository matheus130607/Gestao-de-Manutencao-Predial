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
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class ChamadoResource extends Resource
{
    protected static ?string $model = Chamado::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Operação';

    protected static ?int $navigationSort = 1;

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
                            ->relationship(
                                'responsavel',
                                'name',
                                fn (Builder $query): Builder => $query
                                    ->where('cargo', 'responsavel')
                                    ->when(auth()->user()?->isResponsavel(), fn (Builder $query): Builder => $query->whereKey(auth()->id()))
                            )
                            ->default(fn (): ?int => auth()->user()?->isResponsavel() ? auth()->id() : null)
                            ->disabled(fn (): bool => auth()->user()?->isResponsavel() ?? false)
                            ->dehydrated(true)
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('setor_id')
                            ->label('Setor solicitante')
                            ->relationship(
                                'setor',
                                'nome',
                                fn (Builder $query): Builder => $query->visibleTo(auth()->user())
                            )
                            ->default(fn (): ?int => auth()->user()?->isResponsavel() ? auth()->user()?->setor_id : null)
                            ->disabled(fn (): bool => auth()->user()?->isResponsavel() ?? false)
                            ->dehydrated(true)
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('patrimonio_id')
                            ->label('Patrimônio')
                            ->relationship(
                                'patrimonio',
                                'codigo',
                                fn (Builder $query): Builder => $query->visibleTo(auth()->user())
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->codigo}".($record->nome ? " — {$record->nome}" : ''))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('colaborador_id')
                            ->label('Colaborador executor')
                            ->relationship(
                                'colaborador',
                                'name',
                                fn (Builder $query): Builder => $query
                                    ->where('cargo', 'colaborador')
                                    ->where('ativo', true)
                                    ->orderBy('name')
                            )
                            ->placeholder('Sem colaborador definido')
                            ->searchable()
                            ->preload(),

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
                            ->disabled()
                            ->dehydrated(false)
                            ->hiddenOn('create')
                            ->helperText('O status é alterado pelos botões "Iniciar" e "Concluir" na listagem ou no dashboard.'),

                        Textarea::make('observacao')
                            ->label('Descrição do problema')
                            ->placeholder('Descreva o problema detalhadamente...')
                            ->required()
                            ->columnSpanFull(),

                        FileUpload::make('imagem')
                            ->label('Foto do problema')
                            ->disk('public')
                            ->visibility('public')
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
                    ->getStateUsing(fn (Chamado $record): ?string => $record->publicStoragePath($record->imagem))
                    ->disk('public')
                    ->defaultImageUrl(asset('images/patrimonio-placeholder.svg'))
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

                TextColumn::make('colaborador.name')
                    ->label('Colaborador')
                    ->placeholder('Sem executor')
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

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Chamado::statusOptions()[$state] ?? 'Não informado')
                    ->color(fn (?string $state): string => match ($state) {
                        Chamado::STATUS_ABERTO => 'danger',
                        Chamado::STATUS_EM_ANDAMENTO => 'warning',
                        Chamado::STATUS_CONCLUIDO => 'success',
                        Chamado::STATUS_CANCELADO => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

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

                TextColumn::make('iniciado_em')
                    ->label('Iniciado em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Não iniciado')
                    ->toggleable(),

                TextColumn::make('concluido_em')
                    ->label('Concluído em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Não concluído')
                    ->toggleable(),

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
                    ->label('Setor solicitante')
                    ->relationship('setor', 'nome', fn (Builder $query): Builder => $query->visibleTo(auth()->user()))
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('user_id')
                    ->label('Responsável')
                    ->relationship(
                        'responsavel',
                        'name',
                        fn (Builder $query): Builder => $query
                            ->where('cargo', 'responsavel')
                            ->when(
                                auth()->user()?->isResponsavel() && filled(auth()->user()?->setor_id),
                                fn (Builder $query): Builder => $query->where('setor_id', auth()->user()?->setor_id)
                            )
                    )
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('colaborador_id')
                    ->label('Colaborador')
                    ->relationship('colaborador', 'name', fn (Builder $query): Builder => $query->where('cargo', 'colaborador'))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn (Chamado $record): bool => auth()->user()?->can('view', $record) ?? false),

                Tables\Actions\Action::make('iniciar')
                    ->label('Iniciar chamado')
                    ->icon('heroicon-m-play')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Iniciar chamado')
                    ->modalDescription('Tem certeza que deseja iniciar este chamado? A data e hora de início serão registradas automaticamente.')
                    ->modalSubmitActionLabel('Confirmar início')
                    ->modalCancelActionLabel('Cancelar')
                    ->visible(fn (Chamado $record): bool => auth()->user()?->can('iniciar', $record) ?? false)
                    ->action(fn (Chamado $record): mixed => self::runStatusAction(
                        $record,
                        fn () => $record->iniciar(auth()->user()),
                        "Chamado #{$record->id} em andamento",
                    )),

                Tables\Actions\Action::make('concluir')
                    ->label('Concluir')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Concluir chamado')
                    ->modalDescription('Tem certeza que deseja concluir este chamado? A data e hora de fechamento serão registradas automaticamente.')
                    ->modalSubmitActionLabel('Confirmar conclusão')
                    ->modalCancelActionLabel('Cancelar')
                    ->visible(fn (Chamado $record): bool => auth()->user()?->can('concluir', $record) ?? false)
                    ->action(fn (Chamado $record): mixed => self::runStatusAction(
                        $record,
                        fn () => $record->concluir(auth()->user()),
                        "Chamado #{$record->id} concluído",
                    )),

                Tables\Actions\EditAction::make()
                    ->visible(fn (Chamado $record): bool => auth()->user()?->can('update', $record) ?? false),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Nenhum chamado cadastrado')
            ->emptyStateDescription('Quando houver solicitações de manutenção, elas aparecerão aqui.');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChamados::route('/'),
            'create' => Pages\CreateChamado::route('/create'),
            'view' => Pages\ViewChamado::route('/{record}'),
            'edit' => Pages\EditChamado::route('/{record}/edit'),
        ];
    }

    private static function runStatusAction(Chamado $record, callable $callback, string $successTitle): void
    {
        try {
            $callback();

            Notification::make()
                ->title($successTitle)
                ->success()
                ->send();
        } catch (InvalidArgumentException $exception) {
            Notification::make()
                ->title('Não foi possível atualizar o chamado')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }
}
