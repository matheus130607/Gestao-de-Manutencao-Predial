<?php

namespace App\Providers;

use App\Models\Empresa;
use App\Models\Patrimonio;
use App\Models\Setor;
use App\Models\User;
use App\Policies\EmpresaPolicy;
use App\Policies\PatrimonioPolicy;
use App\Policies\SetorPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Empresa::class, EmpresaPolicy::class);
        Gate::policy(Patrimonio::class, PatrimonioPolicy::class);
        Gate::policy(Setor::class, SetorPolicy::class);
    }
}
