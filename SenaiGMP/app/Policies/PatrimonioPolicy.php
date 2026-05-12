<?php

namespace App\Policies;

use App\Models\Patrimonio;
use App\Models\User;

class PatrimonioPolicy
{
    private function canManage(User $user): bool
    {
        return in_array($user->cargo, ['admin', 'diretor', 'suporte']);
    }

    public function viewAny(User $user): bool
    {
        return $user->ativo;
    }

    public function view(User $user, Patrimonio $patrimonio): bool
    {
        return $user->ativo;
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Patrimonio $patrimonio): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, Patrimonio $patrimonio): bool
    {
        return in_array($user->cargo, ['admin', 'diretor']);
    }

    public function deleteAny(User $user): bool
    {
        return in_array($user->cargo, ['admin', 'diretor']);
    }
}
