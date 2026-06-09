<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatrimonioResource\Pages;
use App\Models\Patrimonio;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PatrimonioResource extends Resource
{
    protected static ?string $model = Patrimonio::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?string $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Patrimônio';
    protected static ?string $pluralModelLabel = 'Patrimônios';

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user?->isAdmin() || $user?->isResponsavel();
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalhes do Patrimônio')
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código')
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('nome')
                            ->label('Nome'),

                        DatePicker::make('data_aquisicao')
                            ->label('Data de Aquisição')
                            ->displayFormat('d/m/Y'),

                        TextInput::make('valor')
                            ->label('Valor')
                            ->numeric()
                            ->prefix('R$'),

                        Select::make('setor_id')
                            ->label('Setor / Localização')
                            ->relationship('setor', 'nome', fn (Builder $query): Builder => $query->visibleTo(auth()->user())->orderBy('nome'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        FileUpload::make('imagem')
                            ->label('Foto')
                            ->disk('public')
                            ->visibility('public')
                            ->image()
                            ->directory('patrimonios-imagens')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('imagem')
                    ->label('Fotografia')
                    ->getStateUsing(fn (Patrimonio $record): ?string => $record->publicStoragePath($record->imagem))
                    ->disk('public')
                    ->defaultImageUrl(asset('images/patrimonio-placeholder.svg'))
                    ->circular(),

                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nome')
                    ->label('Nome')
                    ->placeholder('Sem nome')
                    ->searchable(),

                TextColumn::make('setor.nome')
                    ->label('Setor')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('data_aquisicao')
                    ->label('Data de Aquisição')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('valor')
                    ->label('Valor')
                    ->money('BRL'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatrimonios::route('/'),
            'create' => Pages\CreatePatrimonio::route('/create'),
            'edit' => Pages\EditPatrimonio::route('/{record}/edit'),
        ];
    }
}
