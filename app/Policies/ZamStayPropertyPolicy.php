<?php

namespace App\Policies;

use App\Models\User;

class ZamStayPropertyPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, $property): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, $property): bool
    {
        return $user->id === $property->owner_id;
    }

    public function delete(User $user, $property): bool
    {
        return $user->id === $property->owner_id;
    }
}
