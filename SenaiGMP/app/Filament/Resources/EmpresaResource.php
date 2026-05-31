<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmpresaResource\Pages;
use App\Models\Empresa;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

// Componentes de Formulário
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;

// Componentes de Tabela
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

// API para o CEP e Set para atualizar os campos
use Illuminate\Support\Facades\Http;
use Filament\Forms\Set;

class EmpresaResource extends Resource
{
    public static function canViewAny(): bool
{
    // Apenas Admin e Responsável podem ver este menu
    return in_array(auth()->user()->cargo, ['admin', 'responsavel']);
}

    protected static ?string $model = Empresa::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Empresa';
    protected static ?string $pluralModelLabel = 'Empresas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // BLOCO 1: Dados Principais
                Section::make('Dados Principais')
                    ->description('Informações de contato e identificação da empresa')
                    ->schema([
                        TextInput::make('nome')
                            ->label('Razão Social / Nome Fantasia')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('cnpj')
                            ->label('CNPJ')
                            ->mask('99.999.999/9999-99')
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('telefone')
                            ->label('Telefone')
                            ->mask('(99) 99999-9999'),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->columnSpanFull(),
                    ])->columns(2),

                // BLOCO 2: Endereço
                Section::make('Endereço')
                    ->description('Localização da empresa')
                    ->schema([
                        TextInput::make('cep')
                            ->label('CEP')
                            ->mask('99999-999')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set) {
                                if (blank($state)) {
                                    return;
                                }

                                $cep = preg_replace('/[^0-9]/', '', $state);

                                if (strlen($cep) !== 8) {
                                    return;
                                }

                                $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

                                if ($response->successful() && !isset($response['erro'])) {
                                    $dados = $response->json();

                                    $set('estado', $dados['uf'] ?? null);
                                    $set('cidade', $dados['localidade'] ?? null);
                                    $set('bairro', $dados['bairro'] ?? null);
                                    $set('rua', $dados['logradouro'] ?? null);
                                }
                            }),

                        TextInput::make('estado')
                            ->label('Estado (UF)')
                            ->length(2)
                            ->required()
                            ->placeholder('Ex: SP'),

                        TextInput::make('cidade')
                            ->label('Cidade')
                            ->required()
                            ->columnSpan(2),

                        TextInput::make('bairro')
                            ->label('Bairro')
                            ->columnSpan(2),

                        TextInput::make('rua')
                            ->label('Rua / Logradouro')
                            ->columnSpan(2),

                        TextInput::make('numero')
                            ->label('Número')
                            ->columnSpan(1),

                        TextInput::make('complemento')
                            ->label('Complemento')
                            ->placeholder('Ex: Sala 10, Bloco A')
                            ->columnSpan(1),
                    ])->columns(4)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cnpj')
                    ->label('CNPJ')
                    ->searchable(),

                TextColumn::make('telefone')
                    ->label('Telefone')
                    ->searchable(),

                TextColumn::make('cidade')
                    ->label('Cidade/UF')
                    ->getStateUsing(fn (Empresa $record): string => $record->cidade . ' / ' . $record->estado)
                    ->searchable(['cidade', 'estado'])
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
            'index' => Pages\ListEmpresas::route('/'),
            'create' => Pages\CreateEmpresa::route('/create'),
            'edit' => Pages\EditEmpresa::route('/{record}/edit'),
        ];
    }
}