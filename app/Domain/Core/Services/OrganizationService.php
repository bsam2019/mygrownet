<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Models\Department;
use App\Domain\Core\Models\Organization;
use App\Domain\Core\Models\OrganizationBranch;
use App\Domain\Core\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class OrganizationService
{
    public function create(array $data): Organization
    {
        $data['uuid'] ??= (string) Str::uuid();
        $data['slug'] ??= Str::slug($data['name']);

        return Organization::create($data);
    }

    public function update(Organization $org, array $data): Organization
    {
        $org->update($data);
        return $org->fresh();
    }

    public function archive(Organization $org): void
    {
        $org->update(['status' => 'archived']);
        $org->delete();
    }

    public function addMember(Organization $org, User $user, ?string $status = 'active'): OrganizationMember
    {
        return $org->members()->create([
            'user_id' => $user->id,
            'status' => $status ?? 'active',
            'joined_at' => now(),
        ]);
    }

    public function removeMember(Organization $org, User $user): void
    {
        $org->members()->where('user_id', $user->id)->delete();
    }

    public function getMembers(Organization $org): Collection
    {
        return $org->members()->with('user')->get();
    }

    public function addBranch(Organization $org, array $data): OrganizationBranch
    {
        return $org->branches()->create($data);
    }

    public function addDepartment(Organization $org, array $data): Department
    {
        $data['organization_id'] = $org->id;
        return Department::create($data);
    }

    public function getUserOrganizations(User $user): Collection
    {
        return Organization::where('owner_id', $user->id)
            ->orWhereHas('members', fn($q) => $q->where('user_id', $user->id))
            ->get();
    }
}
