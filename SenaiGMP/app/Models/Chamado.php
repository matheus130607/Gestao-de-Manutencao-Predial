<?php

namespace App\Models;

use App\Models\Concerns\HasPublicStorageFiles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Chamado extends Model
{
    use HasFactory, HasPublicStorageFiles;

    public const STATUS_ABERTO = 'aberto';
    public const STATUS_EM_ANDAMENTO = 'em_andamento';
    public const STATUS_CONCLUIDO = 'concluido';
    public const STATUS_CANCELADO = 'cancelado';

    public const PRIORIDADE_BAIXA = 'baixa';
    public const PRIORIDADE_MEDIA = 'media';
    public const PRIORIDADE_ALTA = 'alta';
    public const PRIORIDADE_EMERGENCIA = 'emergencia';

    protected $attributes = [
        'status' => self::STATUS_ABERTO,
    ];

    protected $fillable = [
        'user_id',
        'colaborador_id',
        'setor_id',
        'patrimonio_id',
        'prioridade',
        'tipo',
        'imagem',
        'observacao',
        'status',
        'prazo',
        'iniciado_em',
        'iniciado_por_id',
        'concluido_em',
        'concluido_por_id',
        'cancelado_em',
        'cancelado_por_id',
    ];

    protected function casts(): array
    {
        return [
            'prazo' => 'date',
            'iniciado_em' => 'datetime',
            'concluido_em' => 'datetime',
            'cancelado_em' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Chamado $chamado): void {
            if (! array_key_exists((string) $chamado->status, self::statusOptions())) {
                throw new InvalidArgumentException("Status de chamado invalido: {$chamado->status}");
            }

            if ($chamado->exists && $chamado->isDirty('status')) {
                $chamado->assertValidStatusTransition(
                    (string) $chamado->getOriginal('status'),
                    (string) $chamado->status,
                );
            }

            if (! $chamado->exists || $chamado->isDirty('status')) {
                $chamado->sincronizarTemposOperacionais();
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_ABERTO => 'Aberto',
            self::STATUS_EM_ANDAMENTO => 'Em andamento',
            self::STATUS_CONCLUIDO => 'Concluído',
            self::STATUS_CANCELADO => 'Cancelado',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function prioridadeOptions(): array
    {
        return [
            self::PRIORIDADE_BAIXA => 'Baixa',
            self::PRIORIDADE_MEDIA => 'Média',
            self::PRIORIDADE_ALTA => 'Alta',
            self::PRIORIDADE_EMERGENCIA => 'Emergência',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function tipoOptions(): array
    {
        return [
            'hidraulica' => 'Hidráulica',
            'eletrica' => 'Elétrica',
            'estrutura' => 'Estrutura',
            'limpeza' => 'Limpeza',
            'alvenaria' => 'Alvenaria/Pedreiro',
            'pintura' => 'Pintura',
            'ar_condicionado' => 'Ar condicionado',
            'marcenaria' => 'Marcenaria',
            'serralheria' => 'Serralheria',
            'manutencao_geral' => 'Manutenção geral',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function statusFinalizados(): array
    {
        return [
            self::STATUS_CONCLUIDO,
            self::STATUS_CANCELADO,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function prioridadesCriticas(): array
    {
        return [
            self::PRIORIDADE_ALTA,
            self::PRIORIDADE_EMERGENCIA,
        ];
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'colaborador_id');
    }

    public function setor(): BelongsTo
    {
        return $this->belongsTo(Setor::class);
    }

    public function patrimonio(): BelongsTo
    {
        return $this->belongsTo(Patrimonio::class);
    }

    public function iniciadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'iniciado_por_id');
    }

    public function concluidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'concluido_por_id');
    }

    public function canceladoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelado_por_id');
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->whereNotIn('status', self::statusFinalizados());
    }

    public function scopeAtrasados(Builder $query): Builder
    {
        return $query
            ->ativos()
            ->whereNotNull('prazo')
            ->whereDate('prazo', '<', now()->toDateString());
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user || ! $user->ativo) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isResponsavel()) {
            return $query->where('user_id', $user->id);
        }

        if ($user->isColaborador()) {
            return $query->where('colaborador_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    public function isVisibleTo(User $user): bool
    {
        if (! $user->ativo) {
            return false;
        }

        return $user->isAdmin()
            || ($user->isResponsavel() && $this->user_id === $user->id)
            || ($user->isColaborador() && $this->colaborador_id === $user->id);
    }

    public function isFinalizado(): bool
    {
        return in_array($this->status, self::statusFinalizados(), true);
    }

    public function isAtrasado(): bool
    {
        return filled($this->prazo)
            && ! $this->isFinalizado()
            && $this->prazo->copy()->endOfDay()->isPast();
    }

    public function isCritico(): bool
    {
        return in_array($this->prioridade, self::prioridadesCriticas(), true);
    }

    public function podeIniciar(): bool
    {
        return $this->status === self::STATUS_ABERTO;
    }

    public function podeConcluir(): bool
    {
        return $this->status === self::STATUS_EM_ANDAMENTO;
    }

    public function podeCancelar(): bool
    {
        return ! $this->isFinalizado();
    }

    public function iniciar(?User $actor = null): void
    {
        if (! $this->podeIniciar()) {
            throw new InvalidArgumentException('Este chamado nao pode ser iniciado.');
        }

        $this->status = self::STATUS_EM_ANDAMENTO;
        $this->iniciado_em ??= now();
        $this->iniciado_por_id = $actor?->id;
        $this->concluido_em = null;
        $this->concluido_por_id = null;
        $this->cancelado_em = null;
        $this->cancelado_por_id = null;
        $this->save();
    }

    public function concluir(?User $actor = null): void
    {
        if (! $this->podeConcluir()) {
            throw new InvalidArgumentException('Este chamado ainda nao foi iniciado.');
        }

        $this->status = self::STATUS_CONCLUIDO;
        $this->concluido_em ??= now();
        $this->concluido_por_id = $actor?->id;
        $this->cancelado_em = null;
        $this->cancelado_por_id = null;
        $this->save();
    }

    public function cancelar(?User $actor = null): void
    {
        if (! $this->podeCancelar()) {
            throw new InvalidArgumentException('Este chamado nao pode ser cancelado.');
        }

        $this->status = self::STATUS_CANCELADO;
        $this->cancelado_em ??= now();
        $this->cancelado_por_id = $actor?->id;
        $this->save();
    }

    public function atualizarStatusOperacional(string $status, ?User $actor = null): string
    {
        if (! array_key_exists($status, self::statusOptions())) {
            throw new InvalidArgumentException("Status de chamado invalido: {$status}");
        }

        match ($status) {
            self::STATUS_EM_ANDAMENTO => $this->iniciar($actor),
            self::STATUS_CONCLUIDO => $this->concluir($actor),
            self::STATUS_CANCELADO => $this->cancelar($actor),
            self::STATUS_ABERTO => throw new InvalidArgumentException('Nao e permitido reabrir chamado por esta acao.'),
        };

        return $status;
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? Str::headline((string) $this->status);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_ABERTO => 'danger',
            self::STATUS_EM_ANDAMENTO => 'warning',
            self::STATUS_CONCLUIDO => 'success',
            self::STATUS_CANCELADO => 'gray',
            default => 'gray',
        };
    }

    public function prioridadeLabel(): string
    {
        return self::prioridadeOptions()[$this->prioridade] ?? Str::headline((string) $this->prioridade);
    }

    public function prioridadeColor(): string
    {
        return match ($this->prioridade) {
            self::PRIORIDADE_BAIXA => 'gray',
            self::PRIORIDADE_MEDIA => 'info',
            self::PRIORIDADE_ALTA => 'warning',
            self::PRIORIDADE_EMERGENCIA => 'danger',
            default => 'gray',
        };
    }

    public function prioridadeHex(): string
    {
        return match ($this->prioridade) {
            self::PRIORIDADE_BAIXA => '#64748b',
            self::PRIORIDADE_MEDIA => '#2563eb',
            self::PRIORIDADE_ALTA => '#f59e0b',
            self::PRIORIDADE_EMERGENCIA => '#dc2626',
            default => '#64748b',
        };
    }

    public function tipoLabel(): string
    {
        return self::tipoOptions()[$this->tipo] ?? 'Não informado';
    }

    public function tipoIcon(): string
    {
        return match ($this->tipo) {
            'hidraulica' => 'heroicon-m-beaker',
            'eletrica' => 'heroicon-m-bolt',
            'estrutura', 'alvenaria' => 'heroicon-m-building-office-2',
            'limpeza' => 'heroicon-m-sparkles',
            'ar_condicionado' => 'heroicon-m-cloud',
            'marcenaria' => 'heroicon-m-wrench-screwdriver',
            default => 'heroicon-m-wrench',
        };
    }

    public function resumo(): string
    {
        return Str::limit(trim((string) $this->observacao), 130);
    }

    private function assertValidStatusTransition(string $from, string $to): void
    {
        if ($from === $to) {
            return;
        }

        $allowed = [
            self::STATUS_ABERTO => [self::STATUS_EM_ANDAMENTO, self::STATUS_CANCELADO],
            self::STATUS_EM_ANDAMENTO => [self::STATUS_CONCLUIDO, self::STATUS_CANCELADO],
            self::STATUS_CONCLUIDO => [],
            self::STATUS_CANCELADO => [],
        ];

        if (! in_array($to, $allowed[$from] ?? [], true)) {
            throw new InvalidArgumentException("Transicao de chamado invalida: {$from} para {$to}");
        }
    }

    private function sincronizarTemposOperacionais(): void
    {
        if ($this->status === self::STATUS_ABERTO) {
            $this->iniciado_em = null;
            $this->iniciado_por_id = null;
            $this->concluido_em = null;
            $this->concluido_por_id = null;
            $this->cancelado_em = null;
            $this->cancelado_por_id = null;
        }

        if ($this->status === self::STATUS_EM_ANDAMENTO) {
            $this->iniciado_em ??= now();
            $this->concluido_em = null;
            $this->concluido_por_id = null;
            $this->cancelado_em = null;
            $this->cancelado_por_id = null;
        }

        if ($this->status === self::STATUS_CONCLUIDO) {
            $this->concluido_em ??= now();
            $this->cancelado_em = null;
            $this->cancelado_por_id = null;
        }

        if ($this->status === self::STATUS_CANCELADO) {
            $this->cancelado_em ??= now();
            $this->concluido_em = null;
            $this->concluido_por_id = null;
        }
    }
}
