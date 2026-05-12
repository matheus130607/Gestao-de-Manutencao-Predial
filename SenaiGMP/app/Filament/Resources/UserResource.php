<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

// Componentes de Formulário
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload; // <-- Adicionado para a foto

// Componentes de Tabela
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn; // <-- Adicionado para a foto
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    // Troquei para o ícone de 'usuários' que faz mais sentido
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Pessoas';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Administrador';
    protected static ?string $pluralModelLabel = 'Administradores';
    protected static ?string $slug = 'administradores';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Dados do Administrador')
                    ->description('Preencha as informações do administrador')
                    ->schema([
                        // <-- CAMPO DE FOTO ADICIONADO AQUI -->
                        FileUpload::make('foto_perfil')
                            ->label('Foto de Perfil')
                            ->image()
                            ->avatar()
                            ->directory('avatares')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->columnSpanFull()
                            ->alignCenter(),

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

                        TextInput::make('telefone')
                            ->label('Telefone/WhatsApp')
                            ->mask('(99) 99999-9999')
                            ->required(),

                        Select::make('cargo')
                            ->label('Cargo / Função')
                            ->options([
                                'admin' => 'Administrador Geral',
                                'diretor' => 'Diretor',
                                'professor' => 'Professor',
                                'suporte' => 'Suporte/Manutenção',
                            ])
                            ->default('admin')
                            ->required(),
                        
                        Toggle::make('ativo')
                            ->label('Usuário ativo')
                            ->default(true),

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
                    ])
                    ->columns(2) // Coloca os campos lado a lado
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                // <-- COLUNA DE FOTO ADICIONADA AQUI -->
                ImageColumn::make('foto_perfil')
                    ->label('Avatar')
                    ->circular(), // Foto redonda na tabela

                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),

                TextColumn::make('cargo')
                    ->label('Cargo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'diretor' => 'warning',
                        'professor' => 'info',
                        'suporte' => 'success', // Mudei para success para dar uma cor diferente do default
                        default => 'gray',
                    })
                    ->sortable(),

                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->filters([
                // Aqui depois podemos colocar filtros (ex: filtrar só os ativos)
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    // Filtra para a aba de Administradores mostrar apenas os admins
   // Filtra para a aba de Administradores mostrar todos os cargos administrativos
public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
{
    return parent::getEloquentQuery()->whereIn('cargo', [
        'admin', 
        'diretor', 
        'professor', 
        'suporte'
    ]);
}
}