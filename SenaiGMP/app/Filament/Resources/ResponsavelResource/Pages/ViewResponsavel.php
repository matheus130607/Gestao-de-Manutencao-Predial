<?php

namespace App\Filament\Resources\ResponsavelResource\Pages;

use App\Filament\Resources\ResponsavelResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewResponsavel extends ViewRecord
{
    protected static string $resource = ResponsavelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
