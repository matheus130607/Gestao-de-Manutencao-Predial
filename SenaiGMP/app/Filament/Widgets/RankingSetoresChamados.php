<?php

namespace App\Filament\Widgets;

use App\Models\Chamado;
use Filament\Widgets\Widget;

class RankingSetoresChamados extends Widget
{
    protected static string $view = 'filament.widgets.ranking-setores-chamados';

    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 2,
        'xl' => 2,
    ];

    /**
     * @return array<int, array{setor: string, total: int, ativos: int, atrasados: int, percent: float}>
     */
    public function getRankingRows(): array
    {
        $totalGeral = max(Chamado::query()->count(), 1);

        return Chamado::query()
            ->leftJoin('setors', 'chamados.setor_id', '=', 'setors.id')
            ->selectRaw(
                "COALESCE(setors.nome, 'Sem setor') as setor,
                COUNT(*) as total,
                SUM(CASE WHEN chamados.status NOT IN (?, ?) THEN 1 ELSE 0 END) as ativos,
                SUM(CASE WHEN chamados.status NOT IN (?, ?) AND chamados.prazo IS NOT NULL AND chamados.prazo < ? THEN 1 ELSE 0 END) as atrasados",
                [
                    Chamado::STATUS_CONCLUIDO,
                    Chamado::STATUS_CANCELADO,
                    Chamado::STATUS_CONCLUIDO,
                    Chamado::STATUS_CANCELADO,
                    now()->toDateString(),
                ]
            )
            ->groupByRaw("COALESCE(setors.nome, 'Sem setor')")
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(fn ($row): array => [
                'setor' => (string) $row->setor,
                'total' => (int) $row->total,
                'ativos' => (int) $row->ativos,
                'atrasados' => (int) $row->atrasados,
                'percent' => round(((int) $row->total / $totalGeral) * 100, 1),
            ])
            ->all();
    }
}
