<x-filament-panels::page>
    <x-filament::section>
        <div class="space-y-4">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-gray-950 dark:text-white">
                    Site institucional
                </h2>
                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                    A página pública do SENAI está disponível fora do painel administrativo.
                </p>
            </div>

            <x-filament::button
                tag="a"
                href="{{ route('home') }}"
                icon="heroicon-m-arrow-top-right-on-square"
                target="_blank"
            >
                Abrir site institucional
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-panels::page>
