<?php

namespace App\Filament\Resources\Patrimonios;

use App\Filament\Resources\Patrimonios\Pages\CreatePatrimonio;
use App\Filament\Resources\Patrimonios\Pages\EditPatrimonio;
use App\Filament\Resources\Patrimonios\Pages\ListPatrimonios;
use App\Filament\Resources\Patrimonios\Schemas\PatrimonioForm;
use App\Filament\Resources\Patrimonios\Tables\PatrimoniosTable;
use App\Models\Patrimonio;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PatrimonioResource extends Resource
{
    protected static ?string $model = Patrimonio::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'patrimonio';

    public static function form(Schema $schema): Schema
    {
        return PatrimonioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PatrimoniosTable::configure($table);
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
            'index' => ListPatrimonios::route('/'),
            'create' => CreatePatrimonio::route('/create'),
            'edit' => EditPatrimonio::route('/{record}/edit'),
        ];
    }
}
