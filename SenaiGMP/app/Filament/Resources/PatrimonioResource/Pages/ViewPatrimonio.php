<?php

namespace App\Filament\Resources\PatrimonioResource\Pages;

use App\Filament\Resources\PatrimonioResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPatrimonio extends ViewRecord
{
    protected static string $resource = PatrimonioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
