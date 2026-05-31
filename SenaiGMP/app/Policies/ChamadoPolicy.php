<?php

namespace App\Policies;

use App\Models\Chamado;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChamadoPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode ver a lista (Resource e Widgets).
     */
    public function viewAny(User $user): bool
    {
        // Todos os usuários ativos podem ver a lista de chamados
        return (bool) $user->ativo;
    }

    /**
     * Determina se o usuário pode ver um chamado específico.
     */
    public function view(User $user, Chamado $chamado): bool
    {
        return (bool) $user->ativo;
    }

    /**
     * Determina quem pode abrir novos chamados.
     */
    public function create(User $user): bool
    {
        return (bool) $user->ativo;
    }

    /**
     * Regras de edição:
     * Admin: Edita tudo.
     * Responsável: Edita qualquer chamado (hierarquia maior).
     * Colaborador: Edita apenas o que ele abriu e se não estiver concluído.
     */
    public function update(User $user, Chamado $chamado): bool
    {
        if (!$user->ativo) return false;

        // Admin e Responsável podem editar qualquer chamado
        if (in_array($user->cargo, User::cargosGestao())) {
            return true;
        }

        // Colaborador: apenas o próprio e se não estiver concluído
        return $user->id === $chamado->user_id && $chamado->status !== Chamado::STATUS_CONCLUIDO;
    }

    /**
     * Determina quem pode excluir.
     */
    public function delete(User $user, Chamado $chamado): bool
    {
        // Apenas Admin tem o poder de excluir registros do sistema
        return $user->cargo === 'admin';
    }
}