<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Arr;

class UserPolicy
{
    use HandlesAuthorization;

    public function create(User $user, array $payload)
    {
        if (!in_array($user->role, [User::SUPERADMIN_ROLE, User::ADMIN_ROLE])) {
            return Response::deny('No tienes permisos para registrar usuarios');
        }

        if (Arr::get($payload, 'role') === User::SUPERADMIN_ROLE && $user->role !== User::SUPERADMIN_ROLE) {
            return Response::deny('No puedes registrar superadmins');
        }
        return Response::allow();
    }

    public function update(User $user, User $targetUser, array $payload)
    {
        if ($user->role === User::GROUP_LEADER_ROLE && $targetUser->id !== $user->id) {
            return Response::deny('No tienes permisos para editar este usuario');
        }

        if ($user->role === User::ADMIN_ROLE && $targetUser->role === User::SUPERADMIN_ROLE) {
            return Response::deny('No puedes editar a un superadmin');
        }

        return Response::allow();
    }

    public function delete(User $user, User $targetUser)
    {
        if ($user->role === User::GROUP_LEADER_ROLE && $targetUser->id !== $user->id) {
            return Response::deny('No tienes permisos para eliminar este usuario');
        }

        if ($user->role === User::ADMIN_ROLE && $targetUser->role !== User::GROUP_LEADER_ROLE) {
            return Response::deny('Solo puedes eliminar jefes de grupo');
        }

        return Response::allow();
    }
}
