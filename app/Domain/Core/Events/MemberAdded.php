<?php

namespace App\Domain\Core\Events;

use App\Domain\Core\Models\Organization;
use App\Domain\Core\Models\OrganizationMember;
use Illuminate\Foundation\Events\Dispatchable;

class MemberAdded
{
    use Dispatchable;

    public function __construct(
        public Organization $organization,
        public OrganizationMember $member,
    ) {}
}
