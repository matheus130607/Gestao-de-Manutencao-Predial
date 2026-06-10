<?php

namespace App\Policies;

use App\Models\Chamado;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChamadoPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return (bool) $user->ativo
            && ($user->isAdmin() || $user->isResponsavel() || $user->isColaborador());
    }

    public function view(User $user, Chamado $chamado): bool
    {
        return $chamado->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return (bool) $user->ativo && ($user->isAdmin() || $user->isResponsavel());
    }

    public function update(User $user, Chamado $chamado): bool
    {
        if (! $user->ativo || ! $chamado->isVisibleTo($user)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isResponsavel()) {
            return ! $chamado->isFinalizado();
        }

        return $user->isColaborador()
            && $chamado->colaborador_id === $user->id
            && ! $chamado->isFinalizado();
    }

    public function iniciar(User $user, Chamado $chamado): bool
    {
        if (! $this->canAccessExecutableChamado($user, $chamado) || ! $chamado->podeIniciar()) {
            return false;
        }

        return $user->isAdmin()
            || ($user->isColaborador() && (
                blank($chamado->colaborador_id) || $chamado->colaborador_id === $user->id
            ));
    }

    public function concluir(User $user, Chamado $chamado): bool
    {
        if (! $this->canAccessExecutableChamado($user, $chamado) || ! $chamado->podeConcluir()) {
            return false;
        }

        return $user->isAdmin()
            || ($user->isColaborador() && $chamado->colaborador_id === $user->id);
    }

    public function delete(User $user, Chamado $chamado): bool
    {
        return (bool) $user->ativo && $user->isAdmin();
    }

    private function canAccessExecutableChamado(User $user, Chamado $chamado): bool
    {
        if (! $user->ativo || ! $chamado->isVisibleTo($user)) {
            return false;
        }

        return $user->isAdmin() || $user->isColaborador();
    }
}
