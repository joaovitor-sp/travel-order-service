<?php

namespace App\Http\Policies;

use App\Domain\Models\TravelOrder;
use App\Domain\Models\User;
use Illuminate\Auth\Access\Response;

class TravelOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TravelOrder $travelOrder): bool
    {
        return $user->is_admin || $travelOrder->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TravelOrder $travelOrder): bool
    {
        return $user->is_admin || $travelOrder->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TravelOrder $travelOrder): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TravelOrder $travelOrder): bool
    {
        return $user->is_admin || $travelOrder->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TravelOrder $travelOrder): bool
    {
        return false;
    }

    // approve
    public function approve(User $user, TravelOrder $travelOrder): bool
    {
        return $user->is_admin;
    }

    // cancel
    public function cancel(User $user, TravelOrder $travelOrder): bool
    {
        return $user->is_admin;
    }
}
