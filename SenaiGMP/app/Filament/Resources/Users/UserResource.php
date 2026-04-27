<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum; // Necessário para a assinatura do navigationIcon

// --- 1. Importações do Schema (A grande mudança do Filament v5) ---
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

// Campos de entrada continuam em Forms
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

use Filament\Resources\Resource;

// --- 2. Importações da Tabela ---
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

// --- 3. Importações de Actions (Botões) unificadas no Filament v5 ---
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Administrador';
    protected static ?string $pluralModelLabel = 'Administradores';

    // CORREÇÃO: Usando Schema em vez de Form
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Administrador')
                    ->description('Preencha as informações do administrador')
                    ->schema([
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
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                        default => 'gray',
                    })
                    ->sortable(),

                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->filters([
                //
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}