<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResponsavelResource\Pages;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ResponsavelResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Administração';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Responsável';
    protected static ?string $pluralModelLabel = 'Responsáveis';

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
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
                Section::make('Dados do Responsável')
                    ->schema([
                        FileUpload::make('foto_perfil')
                            ->label('Avatar')
                            ->disk('public')
                            ->visibility('public')
                            ->image()
                            ->avatar()
                            ->directory('perfil-usuarios')
                            ->columnSpanFull(),

                        Hidden::make('cargo')
                            ->default('responsavel'),

                        TextInput::make('name')
                            ->label('Nome Completo')
                            ->required(),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('cpf')
                            ->label('CPF')
                            ->mask('999.999.999-99')
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('nif')
                            ->label('NIF (Nº de Identificação)')
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('telefone')
                            ->label('Telefone')
                            ->mask('(99) 99999-9999')
                            ->required(),

                        Select::make('empresa_id')
                            ->label('Empresa')
                            ->relationship(
                                name: 'empresa',
                                titleAttribute: 'nome',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->whereNotNull('nome')->orderBy('nome')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('setor_id')
                            ->label('Setor responsável')
                            ->relationship('setor', 'nome', fn (Builder $query): Builder => $query->orderBy('nome'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Toggle::make('ativo')
                            ->label('Usuário ativo')
                            ->default(true),

                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->revealable()
                            ->helperText('Deixe em branco para manter a senha atual.')
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto_perfil')
                    ->label('Avatar')
                    ->disk('public')
                    ->defaultImageUrl(asset('images/avatar-placeholder.svg'))
                    ->circular(),

                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),

                TextColumn::make('nif')
                    ->label('NIF')
                    ->placeholder('Sem NIF')
                    ->searchable(),

                TextColumn::make('empresa.nome')
                    ->label('Empresa')
                    ->placeholder('Sem empresa'),

                TextColumn::make('setor.nome')
                    ->label('Setor')
                    ->placeholder('Sem setor')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean(),
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
        return parent::getEloquentQuery()->where('cargo', 'responsavel');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResponsavels::route('/'),
            'create' => Pages\CreateResponsavel::route('/create'),
            'edit' => Pages\EditResponsavel::route('/{record}/edit'),
        ];
    }
}
