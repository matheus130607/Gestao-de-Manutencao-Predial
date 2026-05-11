<?php

namespace App\Filament\Resources\ResponsavelResource\Pages;

use App\Filament\Resources\ResponsavelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResponsavel extends EditRecord
{
    protected static string $resource = ResponsavelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
