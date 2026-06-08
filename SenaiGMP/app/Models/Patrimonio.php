<?php

namespace App\Models;

use App\Models\Concerns\HasPublicStorageFiles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Patrimonio extends Model
{
    use HasFactory, HasPublicStorageFiles;

    protected $fillable = [
        'codigo',
        'valor',
        'data_aquisicao',
        'imagem',
        'setor_id',
    ];

    public function setor(): BelongsTo
    {
        return $this->belongsTo(Setor::class);
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
            return $query->where('setor_id', $user->setor_id);
        }

        return $query->whereRaw('1 = 0');
    }

    public function isVisibleTo(User $user): bool
    {
        return $user->isAdmin()
            || ($user->isResponsavel() && filled($user->setor_id) && $this->setor_id === $user->setor_id);
    }
}
