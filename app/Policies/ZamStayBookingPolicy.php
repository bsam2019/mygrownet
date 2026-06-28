<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ZamStay\ZamStayBooking;

class ZamStayBookingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ZamStayBooking $booking): bool
    {
        return $user->id === $booking->user_id
            || $user->id === $booking->property->owner_id
            || $user->can('manage-zamstay');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ZamStayBooking $booking): bool
    {
        return $user->id === $booking->user_id
            || $user->id === $booking->property->owner_id;
    }

    public function pay(User $user, ZamStayBooking $booking): bool
    {
        return $user->id === $booking->user_id;
    }

    public function cancel(User $user, ZamStayBooking $booking): bool
    {
        return $user->id === $booking->user_id
            || $user->id === $booking->property->owner_id;
    }

    public function confirm(User $user, ZamStayBooking $booking): bool
    {
        return $user->id === $booking->property->owner_id;
    }
}
