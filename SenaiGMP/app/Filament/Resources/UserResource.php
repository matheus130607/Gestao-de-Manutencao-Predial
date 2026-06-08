<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
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

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Administração';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Administrador';
    protected static ?string $pluralModelLabel = 'Administradores';

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
                Section::make('Dados do Administrador')
                    ->description('Preencha as informações do administrador')
                    ->schema([
                        FileUpload::make('foto_perfil')
                            ->label('Foto de Perfil')
                            ->disk('public')
                            ->visibility('public')
                            ->image()
                            ->avatar()
                            ->directory('avatares')
                            ->columnSpanFull()
                            ->alignCenter(),

                        Hidden::make('cargo')
                            ->default('admin'),

                        TextInput::make('name')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('cpf')
                            ->label('CPF')
                            ->mask('999.999.999-99')
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('nif')
                            ->label('NIF (Nº de Identificação)')
                            ->unique(ignoreRecord: true),

                        TextInput::make('telefone')
                            ->label('Telefone/WhatsApp')
                            ->mask('(99) 99999-9999')
                            ->required(),

                        Select::make('empresa_id')
                            ->label('Empresa')
                            ->relationship('empresa', 'nome', fn (Builder $query): Builder => $query->orderBy('nome'))
                            ->searchable()
                            ->preload(),

                        Select::make('setor_id')
                            ->label('Setor')
                            ->relationship('setor', 'nome', fn (Builder $query): Builder => $query->orderBy('nome'))
                            ->searchable()
                            ->preload(),

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
                            ->maxLength(255)
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
                    ->getStateUsing(fn (User $record): ?string => $record->publicStoragePath($record->foto_perfil))
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
                    ->placeholder('Sem setor'),

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
        return parent::getEloquentQuery()->where('cargo', 'admin');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
