<?php

namespace App\Filament\Resources\ChamadoResource\Pages;

use App\Filament\Resources\ChamadoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateChamado extends CreateRecord
{
    protected static string $resource = ChamadoResource::class;

    public function mount(): void
    {
        parent::mount();

        $patrimonioId = request()->query('patrimonio_id');

        if ($patrimonioId) {
            $this->form->fill([
                'patrimonio_id' => (int) $patrimonioId,
                'user_id'       => auth()->user()?->isResponsavel() ? auth()->id() : null,
                'setor_id'      => auth()->user()?->isResponsavel() ? auth()->user()?->setor_id : null,
            ]);
        }
    }
}
