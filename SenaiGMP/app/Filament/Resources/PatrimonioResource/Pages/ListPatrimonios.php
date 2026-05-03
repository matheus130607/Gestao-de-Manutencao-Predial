<?php

namespace App\Filament\Resources\PatrimonioResource\Pages;

use App\Filament\Resources\PatrimonioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPatrimonios extends ListRecords
{
    protected static string $resource = PatrimonioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
