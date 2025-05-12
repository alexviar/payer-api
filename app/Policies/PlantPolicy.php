<?php

namespace App\Policies;

use App\Models\Plant;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PlantPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Plant $plant): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, array $payload): Response|bool
    {
        if (!in_array($user->role, [User::SUPERADMIN_ROLE, User::ADMIN_ROLE])) {
            return Response::deny('No tienes permiso para registrar plantas de inspección.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Plant $plant): Response|bool
    {
        if (!in_array($user->role, [User::SUPERADMIN_ROLE, User::ADMIN_ROLE])) {
            return Response::deny('No tienes permiso para actualizar plantas de inspección.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Plant $plant): Response|bool
    {
        if (!in_array($user->role, [User::SUPERADMIN_ROLE, User::ADMIN_ROLE])) {
            return Response::deny('No tienes permiso para eliminar plantas de inspección.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Plant $plant): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Plant $plant): bool
    {
        return false;
    }
}
