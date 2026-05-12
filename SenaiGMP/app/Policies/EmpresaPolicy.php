<?php

namespace App\Policies;

use App\Models\Empresa;
use App\Models\User;

class EmpresaPolicy
{
    private function canManage(User $user): bool
    {
        return in_array($user->cargo, ['admin', 'diretor']);
    }

    public function viewAny(User $user): bool
    {
        return $user->ativo;
    }

    public function view(User $user, Empresa $empresa): bool
    {
        return $user->ativo;
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Empresa $empresa): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, Empresa $empresa): bool
    {
        return $user->cargo === 'admin';
    }

    public function deleteAny(User $user): bool
    {
        return $user->cargo === 'admin';
    }
}
