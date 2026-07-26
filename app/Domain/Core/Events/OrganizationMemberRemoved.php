<?php

namespace App\Domain\Core\Events;

use App\Domain\Core\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class OrganizationMemberRemoved
{
    use Dispatchable;

    public function __construct(
        public Organization $organization,
        public User $user,
    ) {}
}
