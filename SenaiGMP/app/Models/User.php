<?php

namespace App\Models;

use App\Models\Concerns\HasPublicStorageFiles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements HasAvatar, FilamentUser
{
    use HasFactory, HasPublicStorageFiles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',
        'nif',
        'telefone',
        'cargo',
        'ativo',
        'empresa_id',
        'foto_perfil',
        'setor_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ativo' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->ativo;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->publicStorageUrl($this->foto_perfil);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function setor(): BelongsTo
    {
        return $this->belongsTo(Setor::class);
    }

    public function especialidadesRelacao(): HasMany
    {
        return $this->hasMany(ColaboradorEspecialidade::class);
    }

    public function isAdmin(): bool
    {
        return $this->cargo === 'admin';
    }

    public function isResponsavel(): bool
    {
        return $this->cargo === 'responsavel';
    }

    public function isColaborador(): bool
    {
        return $this->cargo === 'colaborador';
    }

    public static function cargosGestao(): array
    {
        return ['admin'];
    }
}
