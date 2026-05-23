<?php

namespace App\Policies;

use App\Models\GscProperty;
use App\Models\User;

class GscPropertyPolicy
{
    public function view(User $user, GscProperty $property): bool
    {
        return $user->id === $property->user_id || $user->hasRole('admin');
    }

    public function update(User $user, GscProperty $property): bool
    {
        return $user->id === $property->user_id || $user->hasRole('admin');
    }

    public function delete(User $user, GscProperty $property): bool
    {
        return $user->id === $property->user_id || $user->hasRole('admin');
    }
}
