<?php

namespace App\Policies;

use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\ZamStay\ZamStayAgentModel;

class ZamStayAgentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ZamStayAgentModel $agent): bool
    {
        return $user->id === $agent->user_id;
    }

    public function create(User $user): bool
    {
        return ZamStayAgentModel::where('user_id', $user->id)->doesntExist();
    }

    public function update(User $user, ZamStayAgentModel $agent): bool
    {
        return $user->id === $agent->user_id;
    }

    public function approve(User $user): bool
    {
        return $user->can('manage-zamstay');
    }
}
