<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatrimonioResource\Pages;
use App\Models\Patrimonio;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

// Componentes de Formulário
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;

// Componentes de Tabela
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class PatrimonioResource extends Resource
{
    protected static ?string $model = Patrimonio::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?string $navigationGroup = 'Infraestrutura';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Patrimônio';
    protected static ?string $pluralModelLabel = 'Patrimônios';
    protected static ?string $slug = 'patrimonios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalhes do Património')
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código')
                            ->required()
                            ->unique(ignoreRecord: true),

                        DatePicker::make('data_aquisicao')
                            ->label('Data de Aquisição')
                            ->displayFormat('d/m/Y')
                            ->maxDate(now()),

                        TextInput::make('valor')
                            ->label('Valor')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('R$'),

                        Select::make('setor_id')
                            ->label('Setor / Localização')
                            ->relationship('setor', 'nome') // Puxa do Model 'setor' o campo 'nome'
                            ->searchable() // Permite pesquisar o nome do setor digitando
                            ->preload()    // Carrega a lista antes para ficar rápido
                            ->required(),

                        FileUpload::make('imagem')
                            ->label('Foto')
                            ->image()
                            ->directory('patrimonios-imagens')
                            ->maxSize(4096)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('imagem')
                    ->label('Fotografia')
                    ->circular(),

                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

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
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatrimonios::route('/'),
            'create' => Pages\CreatePatrimonio::route('/create'),
            'view' => Pages\ViewPatrimonio::route('/{record}'),
            'edit' => Pages\EditPatrimonio::route('/{record}/edit'),
        ];
    }
}