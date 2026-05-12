<?php

namespace App\Filament\Resources\ResponsavelResource\Pages;

use App\Filament\Resources\ResponsavelResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateResponsavel extends CreateRecord
{
    protected static string $resource = ResponsavelResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Responsável criado com sucesso!';
    }
}
