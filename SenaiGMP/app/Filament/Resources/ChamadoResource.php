<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChamadoResource\Pages;
use App\Models\Chamado;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;

class ChamadoResource extends Resource
{
    protected static ?string $model = Chamado::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $modelLabel = 'Chamado';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Abertura de Chamado')
                    ->description('Preencha os detalhes da manutenção')
                    ->schema([
                        // Responsável (Filtra apenas quem é "responsavel")
                        Select::make('user_id')
                            ->label('Responsável')
                            ->relationship('responsavel', 'name', fn ($query) => $query->where('cargo', 'responsavel'))
                            // Se o logado for responsável, já vem selecionado, senão fica vazio.
                            ->default(fn () => auth()->user()?->cargo === 'responsavel' ? auth()->id() : null)
                            ->searchable()
                            ->preload()
                            ->required(),

                        // 2. Setor (Escolha manual)
                        Select::make('setor_id')
                            ->label('Setor')
                            ->relationship('setor', 'nome') // Ajuste para 'codigo' se for o caso
                            ->searchable()
                            ->preload()
                            ->required(),

                        // Patrimônio
                        Select::make('patrimonio_id')
                            ->label('Patrimônio')
                            ->relationship('patrimonio', 'codigo')
                            ->helperText(fn ($get) => $get('patrimonio_id') ? "ID do bem selecionado" : null)
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('tipo')
                            ->label('Tipo de Manutenção')
                            ->options([
                                'hidraulica' => 'Hidráulica',
                                'eletrica' => 'Elétrica',
                                'alvenaria' => 'Alvenaria/Pedreiro',
                                'pintura' => 'Pintura',
                                'ar_condicionado' => 'Ar Condicionado',
                                'marcenaria' => 'Marcenaria',
                                'serralheria' => 'Serralheria',
                            ])
                            ->required(),

                        // Prioridade com Cores
                        Select::make('prioridade')
                            ->label('Nível de Prioridade')
                            ->options([
                                'baixa' => 'Baixa',
                                'media' => 'Média',
                                'alta' => 'Alta',
                                'emergencia' => '🚨 EMERGÊNCIA',
                            ])
                            ->required()
                            ->native(false),

                        // Observação
                        Textarea::make('observacao')
                            ->label('O que aconteceu?')
                            ->placeholder('Descreva o problema detalhadamente...')
                            ->required()
                            ->columnSpanFull(),

                        // Upload de Imagem
                        FileUpload::make('imagem')
                            ->label('Foto do Problema')
                            ->image()
                            ->directory('chamados')
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                
                ImageColumn::make('imagem')
                    ->label('Foto')
                    ->square(),

                TextColumn::make('prioridade')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'baixa' => 'gray',
                        'media' => 'info',
                        'alta' => 'warning',
                        'emergencia' => 'danger',
                    }),

                TextColumn::make('responsavel.name')->label('Responsável'),
                TextColumn::make('setor.nome')->label('Setor'),
                TextColumn::make('patrimonio.nome')->label('Patrimônio'),
                
                // Status que pode ser alterado direto na tabela
                SelectColumn::make('status')
                    ->options([
                        'aberto' => 'Aberto',
                        'em_andamento' => 'Em Andamento',
                        'concluido' => 'Concluído',
                    ]),
            ])
            ->filters([])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChamados::route('/'),
            'create' => Pages\CreateChamado::route('/create'),
            'edit' => Pages\EditChamado::route('/{record}/edit'),
        ];
    }
}