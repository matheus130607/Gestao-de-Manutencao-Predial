<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ChamadoResource;
use App\Filament\Widgets\ChamadosEvolucao;
use App\Filament\Widgets\ChamadosPorPrioridade;
use App\Filament\Widgets\ChamadosPorResponsavel;
use App\Filament\Widgets\ChamadosPorSetor;
use App\Filament\Widgets\ChamadosPorTipo;
use App\Filament\Widgets\ChamadosRecentes;
use App\Filament\Widgets\ChamadosStats;
use App\Filament\Widgets\RankingSetoresChamados;
use App\Models\Chamado;
use App\Models\Setor;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Dashboard extends BaseDashboard
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard operacional';

    protected static ?int $navigationSort = -2;

    protected static string $view = 'filament.pages.dashboard';

    protected ?string $maxContentWidth = 'full';

    #[Url(as: 'area')]
    public string $activeArea = 'chamados';

    /**
     * @var array<string, mixed>
     */
    public array $filters = [
        'search' => null,
        'status' => null,
        'prioridade' => null,
        'tipo' => null,
        'setor_id' => null,
        'responsavel_id' => null,
        'data_inicio' => null,
        'data_fim' => null,
        'prazo_situacao' => null,
    ];

    public function mount(): void
    {
        if (! in_array($this->activeArea, ['chamados', 'indicadores'], true)) {
            $this->activeArea = 'chamados';
        }

        $this->form->fill($this->filters);
    }

    public function getHeading(): string
    {
        return 'Visão geral da manutenção predial';
    }

    public function getSubheading(): ?string
    {
        return 'Operação dos chamados, prioridades, prazos e indicadores de desempenho em uma única tela.';
    }

    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 4,
        ];
    }

    public function getWidgets(): array
    {
        return [
            ChamadosStats::class,
            ChamadosEvolucao::class,
            ChamadosPorPrioridade::class,
            ChamadosPorSetor::class,
            ChamadosPorTipo::class,
            ChamadosPorResponsavel::class,
            RankingSetoresChamados::class,
            ChamadosRecentes::class,
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('search')
                    ->label('Pesquisar')
                    ->placeholder('ID, descrição, setor, responsável ou patrimônio')
                    ->prefixIcon('heroicon-m-magnifying-glass')
                    ->live(debounce: 500)
                    ->columnSpan([
                        'default' => 1,
                        'xl' => 2,
                    ]),

                Select::make('status')
                    ->label('Status')
                    ->options(Chamado::statusOptions())
                    ->placeholder('Todos')
                    ->native(false)
                    ->live(),

                Select::make('prioridade')
                    ->label('Prioridade')
                    ->options(Chamado::prioridadeOptions())
                    ->placeholder('Todas')
                    ->native(false)
                    ->live(),

                Select::make('tipo')
                    ->label('Tipo')
                    ->options(Chamado::tipoOptions())
                    ->placeholder('Todos')
                    ->searchable()
                    ->native(false)
                    ->live(),

                Select::make('setor_id')
                    ->label('Setor solicitante')
                    ->options(fn (): array => Setor::query()
                        ->orderBy('nome')
                        ->pluck('nome', 'id')
                        ->all())
                    ->placeholder('Todos')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->live(),

                Select::make('responsavel_id')
                    ->label('Responsável')
                    ->options(fn (): array => User::query()
                        ->where('cargo', 'responsavel')
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->placeholder('Todos')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->live(),

                DatePicker::make('data_inicio')
                    ->label('Abertos de')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->live(),

                DatePicker::make('data_fim')
                    ->label('Abertos até')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->live(),

                Select::make('prazo_situacao')
                    ->label('Prazo')
                    ->options([
                        'atrasados' => 'Atrasados',
                        'dentro_prazo' => 'Dentro do prazo',
                        'sem_prazo' => 'Sem prazo',
                    ])
                    ->placeholder('Todos')
                    ->native(false)
                    ->live(),
            ])
            ->columns([
                'default' => 1,
                'md' => 2,
                'xl' => 4,
            ])
            ->statePath('filters');
    }

    public function updated(string $property, mixed $value = null): void
    {
        if (str_starts_with($property, 'filters.')) {
            $this->resetPage('chamadosPage');
        }
    }

    public function setActiveArea(string $area): void
    {
        if (! in_array($area, ['chamados', 'indicadores'], true)) {
            return;
        }

        $this->activeArea = $area;
        $this->resetPage('chamadosPage');
    }

    public function limparFiltros(): void
    {
        $this->filters = [
            'search' => null,
            'status' => null,
            'prioridade' => null,
            'tipo' => null,
            'setor_id' => null,
            'responsavel_id' => null,
            'data_inicio' => null,
            'data_fim' => null,
            'prazo_situacao' => null,
        ];

        $this->form->fill($this->filters);
        $this->resetPage('chamadosPage');
    }

    public function getChamados(): LengthAwarePaginator
    {
        return $this->getChamadosQuery()
            ->orderByRaw(
                "CASE
                    WHEN status NOT IN (?, ?) AND prioridade = ? THEN 0
                    WHEN status NOT IN (?, ?) AND prioridade = ? THEN 1
                    WHEN status = ? THEN 2
                    WHEN status = ? THEN 3
                    ELSE 4
                END",
                [
                    Chamado::STATUS_CONCLUIDO,
                    Chamado::STATUS_CANCELADO,
                    Chamado::PRIORIDADE_EMERGENCIA,
                    Chamado::STATUS_CONCLUIDO,
                    Chamado::STATUS_CANCELADO,
                    Chamado::PRIORIDADE_ALTA,
                    Chamado::STATUS_ABERTO,
                    Chamado::STATUS_EM_ANDAMENTO,
                ]
            )
            ->orderByRaw('CASE WHEN prazo IS NULL THEN 1 ELSE 0 END')
            ->orderBy('prazo')
            ->latest('created_at')
            ->paginate(9, ['*'], 'chamadosPage');
    }

    /**
     * @return array<int, array{label: string, value: int, icon: string, color: string}>
     */
    public function getQueueSummary(): array
    {
        return [
            [
                'label' => 'Emergências',
                'value' => Chamado::query()
                    ->ativos()
                    ->where('prioridade', Chamado::PRIORIDADE_EMERGENCIA)
                    ->count(),
                'icon' => 'heroicon-m-exclamation-triangle',
                'color' => 'danger',
            ],
            [
                'label' => 'Atrasados',
                'value' => Chamado::query()->atrasados()->count(),
                'icon' => 'heroicon-m-clock',
                'color' => 'warning',
            ],
            [
                'label' => 'Abertos',
                'value' => Chamado::query()
                    ->where('status', Chamado::STATUS_ABERTO)
                    ->count(),
                'icon' => 'heroicon-m-inbox',
                'color' => 'danger',
            ],
            [
                'label' => 'Em andamento',
                'value' => Chamado::query()
                    ->where('status', Chamado::STATUS_EM_ANDAMENTO)
                    ->count(),
                'icon' => 'heroicon-m-wrench-screwdriver',
                'color' => 'warning',
            ],
            [
                'label' => 'Concluídos 7 dias',
                'value' => Chamado::query()
                    ->where('status', Chamado::STATUS_CONCLUIDO)
                    ->where(function (Builder $query): void {
                        $query
                            ->whereDate('concluido_em', '>=', now()->subDays(6)->toDateString())
                            ->orWhere(function (Builder $query): void {
                                $query
                                    ->whereNull('concluido_em')
                                    ->whereDate('updated_at', '>=', now()->subDays(6)->toDateString());
                            });
                    })
                    ->count(),
                'icon' => 'heroicon-m-check-circle',
                'color' => 'success',
            ],
        ];
    }

    public function getActiveChamadosCount(): int
    {
        return Chamado::query()->ativos()->count();
    }

    public function getAppliedFiltersCount(): int
    {
        return collect($this->filters)
            ->filter(fn ($value): bool => filled($value))
            ->count();
    }

    public function executarChamado(int $chamadoId): void
    {
        $chamado = Chamado::query()->findOrFail($chamadoId);

        if (Gate::denies('update', $chamado)) {
            Notification::make()
                ->title('Você não pode executar este chamado')
                ->danger()
                ->send();

            return;
        }

        if (! $chamado->podeIniciar()) {
            Notification::make()
                ->title('Este chamado não está aberto')
                ->body('Atualize a fila para conferir o status atual.')
                ->warning()
                ->send();

            return;
        }

        $chamado->iniciar();

        Notification::make()
            ->title("Chamado #{$chamado->id} em andamento")
            ->success()
            ->send();
    }

    public function concluirChamado(int $chamadoId): void
    {
        $chamado = Chamado::query()->findOrFail($chamadoId);

        if (Gate::denies('update', $chamado)) {
            Notification::make()
                ->title('Você não pode concluir este chamado')
                ->danger()
                ->send();

            return;
        }

        if (! $chamado->podeConcluir()) {
            Notification::make()
                ->title('Este chamado não está em andamento')
                ->body('Atualize a fila para conferir o status atual.')
                ->warning()
                ->send();

            return;
        }

        $chamado->concluir();

        Notification::make()
            ->title("Chamado #{$chamado->id} concluído")
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('novoChamado')
                ->label('Novo chamado')
                ->icon('heroicon-m-plus')
                ->url(ChamadoResource::getUrl('create')),

            Action::make('verChamados')
                ->label('Ver chamados')
                ->icon('heroicon-m-list-bullet')
                ->color('gray')
                ->url(ChamadoResource::getUrl('index')),
        ];
    }

    private function getChamadosQuery(): Builder
    {
        $filters = $this->filters;
        $search = trim((string) ($filters['search'] ?? ''));

        return Chamado::query()
            ->with(['setor', 'responsavel', 'patrimonio'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    if (is_numeric($search)) {
                        $query->orWhereKey((int) $search);
                    }

                    $like = "%{$search}%";

                    $query
                        ->orWhere('observacao', 'like', $like)
                        ->orWhere('tipo', 'like', $like)
                        ->orWhereHas('setor', fn (Builder $query) => $query->where('nome', 'like', $like))
                        ->orWhereHas('responsavel', fn (Builder $query) => $query->where('name', 'like', $like))
                        ->orWhereHas('patrimonio', fn (Builder $query) => $query->where('codigo', 'like', $like));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['prioridade'] ?? null, fn (Builder $query, string $prioridade) => $query->where('prioridade', $prioridade))
            ->when($filters['tipo'] ?? null, fn (Builder $query, string $tipo) => $query->where('tipo', $tipo))
            ->when($filters['setor_id'] ?? null, fn (Builder $query, string $setorId) => $query->where('setor_id', $setorId))
            ->when($filters['responsavel_id'] ?? null, fn (Builder $query, string $responsavelId) => $query->where('user_id', $responsavelId))
            ->when($filters['data_inicio'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['data_fim'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters['prazo_situacao'] ?? null, function (Builder $query, string $situacao): void {
                match ($situacao) {
                    'atrasados' => $query->atrasados(),
                    'dentro_prazo' => $query
                        ->ativos()
                        ->whereNotNull('prazo')
                        ->whereDate('prazo', '>=', now()->toDateString()),
                    'sem_prazo' => $query->whereNull('prazo'),
                    default => null,
                };
            });
    }
}
