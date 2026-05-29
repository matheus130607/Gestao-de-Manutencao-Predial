<x-filament-widgets::widget>
    <x-filament::section
        heading="Ranking de setores"
        description="Áreas com maior volume e chamados ativos"
        icon="heroicon-m-map"
    >
        <div class="space-y-4">
            @forelse ($this->getRankingRows() as $row)
                <div class="space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-950 dark:text-white">
                                {{ $row['setor'] }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $row['total'] }} chamado{{ $row['total'] === 1 ? '' : 's' }} no histórico
                            </p>
                        </div>

                        <div class="flex flex-wrap justify-end gap-2">
                            <x-filament::badge color="info">
                                {{ $row['ativos'] }} ativo{{ $row['ativos'] === 1 ? '' : 's' }}
                            </x-filament::badge>

                            @if ($row['atrasados'] > 0)
                                <x-filament::badge color="danger" icon="heroicon-m-clock">
                                    {{ $row['atrasados'] }} atraso{{ $row['atrasados'] === 1 ? '' : 's' }}
                                </x-filament::badge>
                            @endif
                        </div>
                    </div>

                    <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                        <div
                            class="h-full rounded-full"
                            style="width: {{ $row['percent'] }}%; background-color: #2563eb;"
                        ></div>
                    </div>
                </div>
            @empty
                <div class="grid justify-items-center gap-2 py-8 text-center">
                    <x-filament::icon
                        icon="heroicon-m-chart-bar"
                        class="h-9 w-9 text-gray-400 dark:text-gray-500"
                    />
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Ainda não há chamados para montar o ranking.
                    </p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
