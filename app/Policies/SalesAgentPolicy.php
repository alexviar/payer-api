<?php

namespace App\Policies;

use App\Models\SalesAgent;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalesAgentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Cualquier usuario autenticado puede ver la lista
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SalesAgent $salesAgent): bool
    {
        return true; // Cualquier usuario autenticado puede ver un agente
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [User::ADMIN_ROLE, User::SUPERADMIN_ROLE]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SalesAgent $salesAgent): bool
    {
        // Solo administradores y superadministradores pueden actualizar agentes
        return in_array($user->role, [User::ADMIN_ROLE, User::SUPERADMIN_ROLE]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SalesAgent $salesAgent): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SalesAgent $salesAgent): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SalesAgent $salesAgent): bool
    {
        return false;
    }
}
