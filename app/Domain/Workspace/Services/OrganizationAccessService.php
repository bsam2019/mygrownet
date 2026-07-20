<?php

namespace App\Domain\Workspace\Services;

use App\Domain\Core\Models\Organization;
use App\Domain\Core\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class OrganizationAccessService
{
    public function getAccessibleOrganizations(User $user): Collection
    {
        return Organization::whereHas('members', fn($q) => $q->where('user_id', $user->id)->where('status', 'active'))
            ->with(['installations' => fn($q) => $q->where('status', 'active')->with('application')])
            ->get();
    }

    public function getUserRole(User $user, Organization $org): ?string
    {
        return OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $org->id)
            ->value('role');
    }

    public function validateMembership(User $user, Organization $org): bool
    {
        return OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $org->id)
            ->where('status', 'active')
            ->exists();
    }

    public function getMembers(Organization $org): Collection
    {
        return OrganizationMember::where('organization_id', $org->id)
            ->with('user.profile')
            ->get();
    }
}
