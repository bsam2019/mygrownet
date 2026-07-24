<?php

namespace App\Policies;

use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\ZamStay\ZamStayBookingModel;

class ZamStayBookingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ZamStayBookingModel $booking): bool
    {
        return $user->id === $booking->user_id
            || $user->id === $booking->property->owner_id
            || $user->can('manage-zamstay');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ZamStayBookingModel $booking): bool
    {
        return $user->id === $booking->user_id
            || $user->id === $booking->property->owner_id;
    }

    public function pay(User $user, ZamStayBookingModel $booking): bool
    {
        return $user->id === $booking->user_id;
    }

    public function cancel(User $user, ZamStayBookingModel $booking): bool
    {
        return $user->id === $booking->user_id
            || $user->id === $booking->property->owner_id;
    }

    public function confirm(User $user, ZamStayBookingModel $booking): bool
    {
        return $user->id === $booking->property->owner_id;
    }
}
