<?php

namespace App\Models;

// Imports necessários para o Filament e Permissões
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Support\Facades\Storage; 

class User extends Authenticatable implements HasAvatar, FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',        
        'telefone',   
        'cargo',      
        'ativo',
        'foto_perfil',
        'setor_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ativo' => 'boolean',
        ];
    }

    /**
     * Regra de Acesso ao Painel
     * Bloqueia qualquer usuário que estiver com 'ativo' = false
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Só entra se estiver ativo
        return (bool) $this->ativo;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->foto_perfil ? Storage::url($this->foto_perfil) : null;
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function setor()
    {
        return $this->belongsTo(Setor::class);
    }

    public function especialidadesRelacao(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ColaboradorEspecialidade::class);
    }

    public static function cargosGestao(): array
    {
        return ['admin', 'responsavel'];
    }
}