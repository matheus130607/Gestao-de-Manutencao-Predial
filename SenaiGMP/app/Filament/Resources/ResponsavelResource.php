<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResponsavelResource\Pages;
use App\Models\User; 
use App\Models\Empresa; 
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

// Componentes de Formulário
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\FileUpload; 

// Componentes de Tabela
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn; 
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class ResponsavelResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Pessoas';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Responsável';
    protected static ?string $pluralModelLabel = 'Responsáveis';
    protected static ?string $slug = 'responsaveis';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Dados do Responsável')
                    ->schema([
                        // 1. Avatar no topo
                        FileUpload::make('foto_perfil')
                            ->label('Avatar')
                            ->image()
                            ->avatar()
                            ->directory('perfil-usuarios')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->columnSpanFull(),

                        Hidden::make('cargo')
                            ->default('responsavel'),

                        // 2. Linha 1: Nome e E-mail
                        TextInput::make('name')
                            ->label('Nome Completo')
                            ->required(),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        // 3. Linha 2: Documentos
                        TextInput::make('cpf')
                            ->label('CPF')
                            ->mask('999.999.999-99')
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('nif')
                            ->label('NIF (Nº de Identificação)')
                            ->required()
                            ->unique(ignoreRecord: true),

                        // 4. Linha 3: Contato e Empresa
                        TextInput::make('telefone')
                            ->label('Telefone')
                            ->mask('(99) 99999-9999')
                            ->required(),

                        Select::make('empresa_id')
                            ->label('Empresa')
                            ->relationship(
                                name: 'empresa', 
                                titleAttribute: 'nome',
                                modifyQueryUsing: fn (Builder $query) => $query->whereNotNull('nome')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        // 5. Linha 4: Senha isolada ocupando a linha inteira
                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->revealable()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->minLength(8)
                            ->maxLength(255)
                            ->rules(['regex:/[A-Z]/', 'regex:/[0-9]/'])
                            ->validationMessages([
                                'min' => 'A senha deve ter no mínimo 8 caracteres.',
                                'regex' => 'A senha deve conter ao menos uma letra maiúscula e um número.',
                            ])
                            ->columnSpanFull(),

                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('foto_perfil')
                    ->label('Avatar')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('E-mail'),

                TextColumn::make('nif')
                    ->label('NIF')
                    ->searchable(),

                TextColumn::make('empresa.nome')
                    ->label('Empresa'),
            ])
            ->actions([
                ViewAction::make(),
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
            'view' => Pages\ViewResponsavel::route('/{record}'),
            'edit' => Pages\EditResponsavel::route('/{record}/edit'),
        ];
    }
}