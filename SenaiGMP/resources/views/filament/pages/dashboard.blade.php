<x-filament-panels::page class="fi-dashboard-page">
    <div class="senai-dashboard-shell">
        <div class="senai-dashboard-stack">
            <div class="senai-dashboard-tabs">
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
            </div>

            @if ($activeArea === 'chamados')
                @php
                    $summary = $this->getQueueSummary();
                    $chamados = $this->getChamados();
                    $appliedFilters = $this->getAppliedFiltersCount();
                @endphp

                <div class="senai-summary-grid">
                    @foreach ($summary as $item)
                        <article class="senai-summary-card senai-summary-card--{{ $item['color'] }}">
                            <div class="senai-summary-content">
                                <div>
                                    <p class="senai-summary-label">
                                        {{ $item['label'] }}
                                    </p>
                                    <p class="senai-summary-value">
                                        {{ $item['value'] }}
                                    </p>
                                </div>

                                <x-filament::badge :color="$item['color']" :icon="$item['icon']" />
                            </div>
                        </article>
                    @endforeach
                </div>

                <details class="senai-filter-accordion" @if ($appliedFilters > 0) open @endif>
                    <summary class="senai-filter-summary">
                        <span class="senai-filter-title">
                            <x-filament::icon icon="heroicon-m-funnel" class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                            <span>Filtros avançados</span>

                            @if ($appliedFilters > 0)
                                <x-filament::badge color="primary">
                                    {{ $appliedFilters }} ativo{{ $appliedFilters === 1 ? '' : 's' }}
                                </x-filament::badge>
                            @endif
                        </span>

                        <span class="senai-filter-hint">
                            Expandir/recolher
                            <x-filament::icon icon="heroicon-m-chevron-down" class="senai-filter-chevron" />
                        </span>
                    </summary>

                    <div class="senai-filter-body">
                        <p class="senai-filter-description">
                            Refine a fila por status, prioridade, tipo, setor, responsável, abertura e prazo.
                        </p>

                        <div class="senai-filter-form">
                            {{ $this->form }}
                        </div>

                        <div class="senai-filter-footer">
                            <p class="senai-filter-results">
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
                    <section class="senai-empty-state">
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
                    </section>
                @else
                    <div class="senai-call-grid">
                        @foreach ($chamados as $chamado)
                            @php
                                $canStart = auth()->user()?->can('iniciar', $chamado);
                                $canFinish = auth()->user()?->can('concluir', $chamado);
                                $isAtrasado = $chamado->isAtrasado();
                                $accentColor = $isAtrasado ? '#dc2626' : $chamado->prioridadeHex();
                            @endphp

                            <article class="senai-call-card" wire:key="chamado-card-{{ $chamado->id }}">
                                <div class="h-1" style="background-color: {{ $accentColor }}"></div>

                                <div class="senai-call-card-body">
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
                                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Colaborador</dt>
                                            <dd class="mt-1 truncate text-gray-950 dark:text-white">
                                                {{ $chamado->colaborador?->name ?? 'Sem executor' }}
                                            </dd>
                                        </div>

                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Abertura</dt>
                                            <dd class="mt-1 text-gray-950 dark:text-white">
                                                {{ $chamado->created_at?->format('d/m/Y H:i') }}
                                            </dd>
                                        </div>

                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Início</dt>
                                            <dd class="mt-1 text-gray-950 dark:text-white">
                                                {{ $chamado->iniciado_em?->format('d/m/Y H:i') ?? 'Não iniciado' }}
                                            </dd>
                                        </div>

                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Prazo</dt>
                                            <dd class="mt-1 text-gray-950 dark:text-white">
                                                {{ $chamado->prazo?->format('d/m/Y') ?? 'Sem prazo' }}
                                            </dd>
                                        </div>

                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Fechamento</dt>
                                            <dd class="mt-1 text-gray-950 dark:text-white">
                                                {{ $chamado->concluido_em?->format('d/m/Y H:i') ?? 'Não concluído' }}
                                            </dd>
                                        </div>

                                        <div class="sm:col-span-2">
                                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Patrimônio</dt>
                                            <dd class="mt-1 truncate text-gray-950 dark:text-white">
                                                {{ $chamado->patrimonio?->codigo ?? 'Não informado' }}
                                            </dd>
                                        </div>
                                    </dl>

                                    <div class="senai-call-actions">
                                        <x-filament::button
                                            tag="a"
                                            :href="\App\Filament\Resources\ChamadoResource::getUrl('edit', ['record' => $chamado])"
                                            color="gray"
                                            icon="heroicon-m-arrow-top-right-on-square"
                                            size="sm"
                                        >
                                            Abrir
                                        </x-filament::button>

                                        @if ($canStart)
                                            <x-filament::button
                                                color="warning"
                                                icon="heroicon-m-play"
                                                size="sm"
                                                wire:click="mountAction('iniciarChamado', { chamado: {{ $chamado->id }} })"
                                            >
                                                Iniciar chamado
                                            </x-filament::button>
                                        @endif

                                        @if ($canFinish)
                                            <x-filament::button
                                                color="success"
                                                icon="heroicon-m-check"
                                                size="sm"
                                                wire:click="mountAction('concluirChamado', { chamado: {{ $chamado->id }} })"
                                            >
                                                Concluir
                                            </x-filament::button>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

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
    </div>
</x-filament-panels::page>
