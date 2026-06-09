<?php

namespace App\Filament\Resources\PatrimonioResource\Pages;

use App\Filament\Resources\PatrimonioResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePatrimonio extends CreateRecord
{
    protected static string $resource = PatrimonioResource::class;

    public function mount(): void
    {
        parent::mount();

        $codigo = request()->query('codigo');

        if ($codigo) {
            $this->form->fill([
                'codigo' => $codigo,
            ]);
        }
    }
}
