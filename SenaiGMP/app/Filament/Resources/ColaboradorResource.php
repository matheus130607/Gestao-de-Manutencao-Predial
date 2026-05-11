<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ColaboradorResource\Pages;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

// Componentes
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\CheckboxList; // Novo componente
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn; // Para mostrar as especialidades bonitas na tabela
use Filament\Tables\Actions\EditAction;

class ColaboradorResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $modelLabel = 'Colaborador';
    protected static ?string $pluralModelLabel = 'Colaboradores';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Dados do Colaborador')
                    ->schema([
                        FileUpload::make('foto_perfil')
                            ->label('Avatar')
                            ->image()->avatar()
                            ->directory('perfil-usuarios')
                            ->columnSpanFull(),

                        Hidden::make('cargo')->default('colaborador'),

                        TextInput::make('name')->label('Nome Completo')->required(),
                        TextInput::make('email')->label('E-mail')->email()->required()->unique(ignoreRecord: true),
                        
                        TextInput::make('cpf')
                            ->label('CPF')
                            ->mask('999.999.999-99')
                            ->required()->unique(ignoreRecord: true),

                        TextInput::make('telefone')
                            ->label('Telefone')
                            ->mask('(99) 99999-9999')->required(),

                        Select::make('empresa_id')
                            ->label('Empresa')
                            ->relationship('empresa', 'nome')
                            ->searchable()->preload()->required()
                            ->columnSpanFull(),

                        // --- CAMPO DE ESPECIALIDADES ---
                        CheckboxList::make('especialidades')
                            ->label('Especialidades / Habilidades')
                            ->options([
                                'hidraulica' => 'Hidráulica',
                                'eletrica' => 'Elétrica',
                                'alvenaria' => 'Alvenaria/Pedreiro',
                                'pintura' => 'Pintura',
                                'ar_condicionado' => 'Ar Condicionado',
                                'marcenaria' => 'Marcenaria',
                                'serralheria' => 'Serralheria',
                            ])
                            ->columns(3) // Organiza as opções em 3 colunas
                            ->gridDirection('row')
                            ->columnSpanFull(),

                        TextInput::make('password')
                            ->label('Senha')
                            ->password()->revealable()
                            ->required(fn ($context) => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto_perfil')->label('Avatar')->circular(),
                TextColumn::make('name')->label('Nome')->searchable(),
                
                // Mostra as especialidades como "Badges" (etiquetas) na tabela
                TextColumn::make('especialidades')
                    ->label('Especialidades')
                    ->badge()
                    ->separator(',') // Como é um array, ele separa por vírgula para mostrar
                    ->color('info'),

                TextColumn::make('empresa.nome')->label('Empresa'),
            ])
            ->actions([EditAction::make()]);
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