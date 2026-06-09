<?php

namespace App\Filament\Resources\ColaboradorResource\Pages;

use App\Filament\Resources\ColaboradorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditColaborador extends EditRecord
{
    protected static string $resource = ColaboradorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->sincronizarEspecialidades();
    }

    private function sincronizarEspecialidades(): void
    {
        $selecionadas = $this->form->getRawState()['especialidades_selecionadas'] ?? [];
        $record = $this->getRecord();

        DB::transaction(function () use ($record, $selecionadas) {
            $record->especialidadesRelacao()->delete();
            foreach ($selecionadas as $especialidade) {
                $record->especialidadesRelacao()->create([
                    'especialidade' => $especialidade,
                ]);
            }
        });
    }
}
