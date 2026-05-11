<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SetorResource\Pages;
use App\Models\Setor;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

// Componentes de Formulário
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

// Componentes de Tabela
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class SetorResource extends Resource
{
    protected static ?string $model = Setor::class;

    protected static ?string $navigationIcon = 'heroicon-o-map'; // Ícone de mapa/localização
    protected static ?string $modelLabel = 'Setor';
    protected static ?string $pluralModelLabel = 'Setores';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Identificação do Setor')
                    ->description('Informe a localização exata do setor')
                    ->schema([
                        TextInput::make('nome')
                            ->label('Nome do Setor / Sala')
                            ->placeholder('Ex: Sala A, Recepção, TI')
                            ->required(),

                        TextInput::make('andar')
                            ->label('Andar')
                            ->placeholder('Ex: Térreo, 2º Andar')
                            ->required(),

                        
                        Select::make('bloco')
                            ->label('Bloco')
                            ->options([
                                'A' => 'Bloco A',
                                'B' => 'Bloco B',
                                'C' => 'Bloco C',
                                'D' => 'Bloco D',
                            ])
                            ->required()
                            ->native(false), // Deixa o visual mais moderno
                    ])->columns(3) // Coloca os 3 campos na mesma linha
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->label('Setor/Sala')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('andar')
                    ->label('Andar')
                    ->sortable(),

                TextColumn::make('bloco')
                    ->label('Bloco')
                    ->badge() // Deixa com visual de etiqueta
                    ->color('info')
                    ->sortable(),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSetors::route('/'),
            'create' => Pages\CreateSetor::route('/create'),
            'edit' => Pages\EditSetor::route('/{record}/edit'),
        ];
    }
}