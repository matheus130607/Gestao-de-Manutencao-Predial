<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ColaboradorResource\Pages;
use App\Models\Chamado;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ColaboradorResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Colaborador';
    protected static ?string $pluralModelLabel = 'Colaboradores';

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Dados do Colaborador')
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
                            ->default('colaborador'),

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
                            ->unique(ignoreRecord: true),

                        TextInput::make('telefone')
                            ->label('Telefone')
                            ->mask('(99) 99999-9999')
                            ->required(),

                        Select::make('empresa_id')
                            ->label('Empresa')
                            ->relationship('empresa', 'nome', fn (Builder $query): Builder => $query->orderBy('nome'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('setor_id')
                            ->label('Setor')
                            ->relationship('setor', 'nome', fn (Builder $query): Builder => $query->orderBy('nome'))
                            ->searchable()
                            ->preload(),

                        CheckboxList::make('especialidades_selecionadas')
                            ->label('Especialidades / Habilidades')
                            ->options(Chamado::tipoOptions())
                            ->columns(3)
                            ->gridDirection('row')
                            ->columnSpanFull()
                            ->afterStateHydrated(function (CheckboxList $component, ?User $record): void {
                                if ($record) {
                                    $component->state(
                                        $record->especialidadesRelacao()->pluck('especialidade')->toArray()
                                    );
                                }
                            })
                            ->dehydrated(false),

                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->revealable()
                            ->helperText('Deixe em branco para manter a senha atual.')
                            ->required(fn ($context): bool => $context === 'create')
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

                TextColumn::make('especialidadesRelacao.especialidade')
                    ->label('Especialidades')
                    ->badge()
                    ->color('info'),

                TextColumn::make('empresa.nome')
                    ->label('Empresa')
                    ->placeholder('Sem empresa'),

                TextColumn::make('setor.nome')
                    ->label('Setor')
                    ->placeholder('Sem setor')
                    ->badge()
                    ->color('gray'),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('cargo', 'colaborador');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListColaboradors::route('/'),
            'create' => Pages\CreateColaborador::route('/create'),
            'edit' => Pages\EditColaborador::route('/{record}/edit'),
        ];
    }
}
