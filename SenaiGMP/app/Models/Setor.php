<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Setor extends Model
{
    protected $fillable = [
        'nome',
        'andar',
        'bloco',
    ];

    public function patrimonios(): HasMany
    {
        return $this->hasMany(Patrimonio::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user || ! $user->ativo) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isResponsavel() && filled($user->setor_id)) {
            return $query->whereKey($user->setor_id);
        }

        return $query->whereRaw('1 = 0');
    }
}
