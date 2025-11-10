<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WineDish;
use Illuminate\Auth\Access\HandlesAuthorization;

class WineDishPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_wine::dish');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WineDish $wineDish): bool
    {
        return $user->can('view_wine::dish');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_wine::dish');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WineDish $wineDish): bool
    {
        return $user->can('update_wine::dish');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WineDish $wineDish): bool
    {
        return $user->can('delete_wine::dish');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_wine::dish');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, WineDish $wineDish): bool
    {
        return $user->can('force_delete_wine::dish');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_wine::dish');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, WineDish $wineDish): bool
    {
        return $user->can('restore_wine::dish');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_wine::dish');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, WineDish $wineDish): bool
    {
        return $user->can('replicate_wine::dish');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_wine::dish');
    }
}
