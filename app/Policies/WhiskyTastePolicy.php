<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WhiskyTaste;
use Illuminate\Auth\Access\HandlesAuthorization;

class WhiskyTastePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_whisky::beer::taste');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WhiskyTaste $whiskyTaste): bool
    {
        return $user->can('view_whisky::beer::taste');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_whisky::beer::taste');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WhiskyTaste $whiskyTaste): bool
    {
        return $user->can('update_whisky::beer::taste');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WhiskyTaste $whiskyTaste): bool
    {
        return $user->can('delete_whisky::beer::taste');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_whisky::beer::taste');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, WhiskyTaste $whiskyTaste): bool
    {
        return $user->can('force_delete_whisky::beer::taste');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_whisky::beer::taste');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, WhiskyTaste $whiskyTaste): bool
    {
        return $user->can('restore_whisky::beer::taste');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_whisky::beer::taste');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, WhiskyTaste $whiskyTaste): bool
    {
        return $user->can('replicate_whisky::beer::taste');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_whisky::beer::taste');
    }
}
