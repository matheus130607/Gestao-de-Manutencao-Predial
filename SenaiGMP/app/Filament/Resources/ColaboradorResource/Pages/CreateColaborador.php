<?php

namespace App\Filament\Resources\ColaboradorResource\Pages;

use App\Filament\Resources\ColaboradorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateColaborador extends CreateRecord
{
    protected static string $resource = ColaboradorResource::class;

    protected function afterCreate(): void
    {
        $this->sincronizarEspecialidades();
    }

    private function sincronizarEspecialidades(): void
    {
        $selecionadas = $this->form->getRawState()['especialidades_selecionadas'] ?? [];
        $record = $this->getRecord();

        $record->especialidadesRelacao()->delete();
        foreach ($selecionadas as $especialidade) {
            $record->especialidadesRelacao()->create([
                'especialidade' => $especialidade,
            ]);
        }
    }
}
