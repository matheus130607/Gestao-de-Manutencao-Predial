<?php

namespace App\Filament\Resources\ChamadoResource\Pages;

use App\Filament\Resources\ChamadoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewChamado extends ViewRecord
{
    protected static string $resource = ChamadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn (): bool => auth()->user()?->can('update', $this->getRecord()) ?? false),
        ];
    }
}
