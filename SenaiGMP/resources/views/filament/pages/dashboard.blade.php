<x-filament-panels::page class="fi-dashboard-page">
    <div class="space-y-6">
        <x-filament::tabs label="Áreas do dashboard">
            <x-filament::tabs.item
                icon="heroicon-m-wrench-screwdriver"
                :active="$activeArea === 'chamados'"
                wire:click="setActiveArea('chamados')"
            >
                Área de chamados

                <x-slot name="badge">
                    {{ $this->getActiveChamadosCount() }}
                </x-slot>
            </x-filament::tabs.item>

            <x-filament::tabs.item
                icon="heroicon-m-chart-bar-square"
                :active="$activeArea === 'indicadores'"
                wire:click="setActiveArea('indicadores')"
            >
                Gráficos e indicadores
            </x-filament::tabs.item>
        </x-filament::tabs>

        @if ($activeArea === 'chamados')
            @php
                $summary = $this->getQueueSummary();
                $chamados = $this->getChamados();
                $appliedFilters = $this->getAppliedFiltersCount();
            @endphp

            <x-filament::grid :default="1" :sm="2" :xl="5" class="gap-4">
                @foreach ($summary as $item)
                    <x-filament::grid.column>
                        <div class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                            <div class="flex items-start justify-between gap-3">
                                <div class="grid gap-1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ $item['label'] }}
                                    </p>
                                    <p class="text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                                        {{ $item['value'] }}
                                    </p>
                                </div>

                                <x-filament::badge :color="$item['color']" :icon="$item['icon']" />
                            </div>
                        </div>
                    </x-filament::grid.column>
                @endforeach
            </x-filament::grid>

            <details class="rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <summary class="cursor-pointer select-none px-5 py-4 text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center justify-between gap-2 list-none">
                    <span class="flex items-center gap-2">
                        <x-filament::icon icon="heroicon-m-funnel" class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                        Filtros avançados
                        @if ($appliedFilters > 0)
                            <x-filament::badge color="primary">{{ $appliedFilters }} ativo{{ $appliedFilters === 1 ? '' : 's' }}</x-filament::badge>
                        @endif
                    </span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">clique para expandir</span>
                </summary>

                <div class="border-t border-gray-200 px-5 pb-5 pt-4 dark:border-white/10 space-y-4">
                    {{ $this->form }}

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $chamados->total() }} chamado{{ $chamados->total() === 1 ? '' : 's' }} encontrado{{ $chamados->total() === 1 ? '' : 's' }}
                            @if ($appliedFilters > 0)
                                com {{ $appliedFilters }} filtro{{ $appliedFilters === 1 ? '' : 's' }} ativo{{ $appliedFilters === 1 ? '' : 's' }}
                            @endif
                        </p>

                        <x-filament::button
                            color="gray"
                            icon="heroicon-m-x-mark"
                            size="sm"
                            wire:click="limparFiltros"
                        >
                            Limpar filtros
                        </x-filament::button>
                    </div>
                </div>
            </details>

            @if ($chamados->isEmpty())
                <x-filament::section>
                    <div class="grid justify-items-center gap-3 py-10 text-center">
                        <x-filament::icon
                            icon="heroicon-m-inbox"
                            class="h-10 w-10 text-gray-400 dark:text-gray-500"
                        />
                        <div class="grid gap-1">
                            <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                                Nenhum chamado encontrado
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Ajuste os filtros ou abra um novo chamado.
                            </p>
                        </div>
                    </div>
                </x-filament::section>
            @else
                <x-filament::grid :default="1" :md="2" :xl="3" class="gap-6">
                    @foreach ($chamados as $chamado)
                        @php
                            $canUpdate = auth()->user()?->can('update', $chamado);
                            $isAtrasado = $chamado->isAtrasado();
                            $accentColor = $isAtrasado ? '#dc2626' : $chamado->prioridadeHex();
                        @endphp

                        <x-filament::grid.column wire:key="chamado-card-{{ $chamado->id }}">
                            <article class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 transition dark:bg-gray-900 dark:ring-white/10">
                                <div class="h-1" style="background-color: {{ $accentColor }}"></div>

                                <div class="space-y-4 p-5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                                                Chamado #{{ $chamado->id }}
                                            </p>
                                            <h3 class="mt-1 line-clamp-2 text-base font-semibold text-gray-950 dark:text-white">
                                                {{ $chamado->resumo() }}
                                            </h3>
                                        </div>

                                        <x-filament::badge
                                            :color="$chamado->statusColor()"
                                            icon="heroicon-m-signal"
                                        >
                                            {{ $chamado->statusLabel() }}
                                        </x-filament::badge>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <x-filament::badge
                                            :color="$chamado->prioridadeColor()"
                                            icon="heroicon-m-bell-alert"
                                        >
                                            {{ $chamado->prioridadeLabel() }}
                                        </x-filament::badge>

                                        <x-filament::badge color="gray" :icon="$chamado->tipoIcon()">
                                            {{ $chamado->tipoLabel() }}
                                        </x-filament::badge>

                                        @if ($isAtrasado)
                                            <x-filament::badge color="danger" icon="heroicon-m-clock">
                                                Atrasado
                                            </x-filament::badge>
                                        @elseif ($chamado->isCritico() && ! $chamado->isFinalizado())
                                            <x-filament::badge color="warning" icon="heroicon-m-exclamation-triangle">
                                                Atenção
                                            </x-filament::badge>
                                        @endif
                                    </div>

                                    <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Setor</dt>
                                            <dd class="mt-1 truncate text-gray-950 dark:text-white">
                                                {{ $chamado->setor?->nome ?? 'Sem setor' }}
                                            </dd>
                                        </div>

                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Responsável</dt>
                                            <dd class="mt-1 truncate text-gray-950 dark:text-white">
                                                {{ $chamado->responsavel?->name ?? 'Não atribuído' }}
                                            </dd>
                                        </div>

                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Abertura</dt>
                                            <dd class="mt-1 text-gray-950 dark:text-white">
                                                {{ $chamado->created_at?->format('d/m/Y H:i') }}
                                            </dd>
                                        </div>

                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Prazo</dt>
                                            <dd class="mt-1 text-gray-950 dark:text-white">
                                                {{ $chamado->prazo?->format('d/m/Y') ?? 'Sem prazo' }}
                                            </dd>
                                        </div>

                                        <div class="sm:col-span-2">
                                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Patrimônio</dt>
                                            <dd class="mt-1 truncate text-gray-950 dark:text-white">
                                                {{ $chamado->patrimonio?->codigo ?? 'Não informado' }}
                                            </dd>
                                        </div>
                                    </dl>

                                    <div class="flex flex-col gap-2 border-t border-gray-200 pt-4 dark:border-white/10 sm:flex-row">
                                        <x-filament::button
                                            tag="a"
                                            :href="\App\Filament\Resources\ChamadoResource::getUrl('edit', ['record' => $chamado])"
                                            color="gray"
                                            icon="heroicon-m-arrow-top-right-on-square"
                                            size="sm"
                                        >
                                            Abrir
                                        </x-filament::button>

                                        @if ($canUpdate && $chamado->podeIniciar())
                                            <x-filament::button
                                                color="warning"
                                                icon="heroicon-m-play"
                                                size="sm"
                                                wire:click="executarChamado({{ $chamado->id }})"
                                                wire:target="executarChamado({{ $chamado->id }})"
                                            >
                                                Executar
                                            </x-filament::button>
                                        @endif

                                        @if ($canUpdate && $chamado->podeConcluir())
                                            <x-filament::button
                                                color="success"
                                                icon="heroicon-m-check"
                                                size="sm"
                                                wire:click="concluirChamado({{ $chamado->id }})"
                                                wire:target="concluirChamado({{ $chamado->id }})"
                                            >
                                                Concluir
                                            </x-filament::button>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        </x-filament::grid.column>
                    @endforeach
                </x-filament::grid>

                @if ($chamados->hasPages())
                    <div>
                        {{ $chamados->onEachSide(1)->links() }}
                    </div>
                @endif
            @endif
        @else
            <x-filament-widgets::widgets
                :columns="$this->getColumns()"
                :data="$this->getWidgetData()"
                :widgets="$this->getVisibleWidgets()"
            />
        @endif
    </div>
</x-filament-panels::page>
