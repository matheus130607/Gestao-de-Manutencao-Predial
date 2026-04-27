<?php

namespace App\Filament\Resources\Patrimonios\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PatrimonioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('codigo')
                    ->required(),
                TextInput::make('nome')
                    ->required(),
                TextInput::make('categoria')
                    ->required(),
            ]);
    }
}
