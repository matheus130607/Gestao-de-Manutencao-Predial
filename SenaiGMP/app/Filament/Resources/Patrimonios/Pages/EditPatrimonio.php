<?php

namespace App\Filament\Resources\Patrimonios\Pages;

use App\Filament\Resources\Patrimonios\PatrimonioResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPatrimonio extends EditRecord
{
    protected static string $resource = PatrimonioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
