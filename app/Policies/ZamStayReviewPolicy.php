<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ZamStay\ZamStayReview;

class ZamStayReviewPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ZamStayReview $review): bool
    {
        return $user->id === $review->user_id;
    }

    public function delete(User $user, ZamStayReview $review): bool
    {
        return $user->id === $review->user_id;
    }
}
