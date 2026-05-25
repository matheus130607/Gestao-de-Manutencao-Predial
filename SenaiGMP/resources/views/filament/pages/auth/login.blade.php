<div>
    <div class="mb-9 flex justify-center">
        <div class="gmp-login-logo-frame">
            <img
                src="{{ asset('senai-logo.jpg') }}"
                alt="SENAI GMP"
                class="gmp-login-logo gmp-login-logo--light rounded-sm"
            >
            <img
                src="{{ asset('logos_senai_preto.png') }}"
                alt="SENAI GMP"
                class="gmp-login-logo gmp-login-logo--dark rounded-sm"
            >
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
            {{ $this->getHeading() }}
        </h2>
        @if ($sub = $this->getSubheading())
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $sub }}</p>
        @endif
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(
        \Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
        scopes: $this->getRenderHookScopes()
    ) }}

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}
        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(
        \Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
        scopes: $this->getRenderHookScopes()
    ) }}

    @if (filament()->hasRegistration())
        <p class="mt-6 text-center text-sm text-gray-500">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}
            {{ $this->registerAction }}
        </p>
    @endif

    <x-filament-actions::modals />
</div>
