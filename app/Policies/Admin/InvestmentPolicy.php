<?php

namespace App\Policies\Admin;

use App\Models\User;
use App\Models\Investment;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvestmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasRole('Administrator') || $user->hasRole('admin');
    }

    public function view(User $user, Investment $investment)
    {
        return $user->hasRole('Administrator') || $user->hasRole('admin');
    }

    public function approve(User $user, Investment $investment)
    {
        return ($user->hasRole('Administrator') || $user->hasRole('admin')) && $investment->status === 'pending';
    }

    public function reject(User $user, Investment $investment)
    {
        return ($user->hasRole('Administrator') || $user->hasRole('admin')) && 
               $investment->status === 'pending' && 
               !$investment->rejected_at;
    }
}
