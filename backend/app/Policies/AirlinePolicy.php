<?php

namespace App\Policies;

use App\Models\Airline;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AirlinePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Anyone can see the list of airlines
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Airline $airline): bool
    {
        return true; // Anyone can view airline details
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isEnterprise(); // Only enterprise users can create airlines
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Airline $airline): bool
    {
        // Only the enterprise that owns the airline can update it
        return $user->isEnterprise() && $user->id === $airline->enterprise_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Airline $airline): bool
    {
        // Only the enterprise that owns the airline can delete it
        return $user->isEnterprise() && $user->id === $airline->enterprise_id;
    }
}
