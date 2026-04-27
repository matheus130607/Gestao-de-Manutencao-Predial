<?php

namespace App\Filament\Resources\Patrimonios\Pages;

use App\Filament\Resources\Patrimonios\PatrimonioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPatrimonios extends ListRecords
{
    protected static string $resource = PatrimonioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
