<?php

namespace App\Policies;

use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\ZamStay\ZamStayReviewModel;

class ZamStayReviewPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ZamStayReviewModel $review): bool
    {
        return $user->id === $review->user_id;
    }

    public function delete(User $user, ZamStayReviewModel $review): bool
    {
        return $user->id === $review->user_id;
    }
}
