<?php

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\TeamMember;
use App\Domain\GrowFinance\Repositories\TeamMemberRepositoryInterface;
use App\Domain\Module\Services\SubscriptionService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeamService
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private TeamMemberRepositoryInterface $teamMemberRepo,
    ) {}

    public function getTeamMembers(int $businessId): array
    {
        $members = $this->teamMemberRepo->findByBusiness($businessId);

        usort($members, fn(TeamMember $a, TeamMember $b) =>
            ($a->role ?? '') <=> ($b->role ?? '') ?:
            (($a->createdAt?->getTimestamp() ?? 0) <=> ($b->createdAt?->getTimestamp() ?? 0))
        );

        return array_map(fn(TeamMember $m) => $m->toArray(), $members);
    }

    public function canAddTeamMember(int $ownerId): array
    {
        $owner = User::findOrFail($ownerId);
        $limits = $this->subscriptionService->getUserLimits($owner);
        $maxMembers = $limits['team_members'] ?? 0;

        if ($maxMembers === 0) {
            return [
                'allowed' => false,
                'reason' => 'Multi-user access is available on Business plan. Please upgrade.',
            ];
        }

        $currentMembers = $this->teamMemberRepo->findByBusiness($ownerId);
        $currentCount = count(array_filter(
            $currentMembers,
            fn(TeamMember $m) => in_array($m->status, ['active', 'pending'])
        ));

        if ($maxMembers !== -1 && $currentCount >= $maxMembers) {
            return [
                'allowed' => false,
                'reason' => "Team member limit reached ({$maxMembers}). Upgrade for more seats.",
                'used' => $currentCount,
                'limit' => $maxMembers,
            ];
        }

        return [
            'allowed' => true,
            'used' => $currentCount,
            'limit' => $maxMembers,
        ];
    }

    public function inviteTeamMember(
        int $businessId,
        string $email,
        string $role = 'viewer',
        ?array $permissions = null
    ): array {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return [
                'success' => false,
                'error' => 'User not found. They must register on the platform first.',
            ];
        }

        $existingMembers = $this->teamMemberRepo->findByBusiness($businessId);
        $existing = null;
        foreach ($existingMembers as $m) {
            if ($m->userId === $user->id) {
                $existing = $m;
                break;
            }
        }

        if ($existing) {
            return [
                'success' => false,
                'error' => 'This user is already a team member.',
            ];
        }

        $token = Str::random(32);

        $member = $this->teamMemberRepo->save(new TeamMember(
            id: null,
            businessId: $businessId,
            userId: $user->id,
            role: $role,
            permissions: $permissions,
            status: 'pending',
            invitationToken: $token,
            invitedAt: new \DateTimeImmutable('now'),
            acceptedAt: null,
            createdAt: null,
            updatedAt: null,
        ));

        return [
            'success' => true,
            'member' => $member->toArray(),
            'token' => $token,
        ];
    }

    public function acceptInvitation(string $token): array
    {
        $memberRow = DB::table('growfinance_team_members')
            ->where('invitation_token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$memberRow) {
            return [
                'success' => false,
                'error' => 'Invalid or expired invitation.',
            ];
        }

        $member = TeamMember::reconstitute((array) $memberRow);

        $updated = new TeamMember(
            id: $member->id,
            businessId: $member->businessId,
            userId: $member->userId,
            role: $member->role,
            permissions: $member->permissions,
            status: 'active',
            invitationToken: null,
            invitedAt: $member->invitedAt,
            acceptedAt: new \DateTimeImmutable('now'),
            createdAt: $member->createdAt,
            updatedAt: new \DateTimeImmutable('now'),
        );
        $this->teamMemberRepo->save($updated);

        return [
            'success' => true,
            'member' => $updated->toArray(),
        ];
    }

    public function updateTeamMember(
        int $businessId,
        int $memberId,
        string $role,
        ?array $permissions = null
    ): array {
        $member = $this->teamMemberRepo->findById($memberId);

        if (!$member || $member->businessId !== $businessId) {
            return [
                'success' => false,
                'error' => 'Team member not found.',
            ];
        }

        if ($member->role === 'owner') {
            return [
                'success' => false,
                'error' => 'Cannot modify owner role.',
            ];
        }

        $updated = new TeamMember(
            id: $member->id,
            businessId: $member->businessId,
            userId: $member->userId,
            role: $role,
            permissions: $permissions,
            status: $member->status,
            invitationToken: $member->invitationToken,
            invitedAt: $member->invitedAt,
            acceptedAt: $member->acceptedAt,
            createdAt: $member->createdAt,
            updatedAt: new \DateTimeImmutable('now'),
        );
        $this->teamMemberRepo->save($updated);

        return [
            'success' => true,
            'member' => $updated->toArray(),
        ];
    }

    public function removeTeamMember(int $businessId, int $memberId): array
    {
        $member = $this->teamMemberRepo->findById($memberId);

        if (!$member || $member->businessId !== $businessId) {
            return [
                'success' => false,
                'error' => 'Team member not found.',
            ];
        }

        if ($member->role === 'owner') {
            return [
                'success' => false,
                'error' => 'Cannot remove the business owner.',
            ];
        }

        DB::table('growfinance_team_members')->where('id', $memberId)->delete();

        return ['success' => true];
    }

    public function suspendTeamMember(int $businessId, int $memberId): array
    {
        $member = $this->teamMemberRepo->findById($memberId);

        if (!$member || $member->businessId !== $businessId) {
            return [
                'success' => false,
                'error' => 'Team member not found.',
            ];
        }

        if ($member->role === 'owner') {
            return [
                'success' => false,
                'error' => 'Cannot suspend the business owner.',
            ];
        }

        $updated = new TeamMember(
            id: $member->id,
            businessId: $member->businessId,
            userId: $member->userId,
            role: $member->role,
            permissions: $member->permissions,
            status: 'suspended',
            invitationToken: $member->invitationToken,
            invitedAt: $member->invitedAt,
            acceptedAt: $member->acceptedAt,
            createdAt: $member->createdAt,
            updatedAt: new \DateTimeImmutable('now'),
        );
        $this->teamMemberRepo->save($updated);

        return ['success' => true, 'member' => $updated->toArray()];
    }

    public function reactivateTeamMember(int $businessId, int $memberId): array
    {
        $member = $this->teamMemberRepo->findById($memberId);

        if (!$member || $member->businessId !== $businessId || $member->status !== 'suspended') {
            return [
                'success' => false,
                'error' => 'Team member not found or not suspended.',
            ];
        }

        $updated = new TeamMember(
            id: $member->id,
            businessId: $member->businessId,
            userId: $member->userId,
            role: $member->role,
            permissions: $member->permissions,
            status: 'active',
            invitationToken: $member->invitationToken,
            invitedAt: $member->invitedAt,
            acceptedAt: $member->acceptedAt,
            createdAt: $member->createdAt,
            updatedAt: new \DateTimeImmutable('now'),
        );
        $this->teamMemberRepo->save($updated);

        return ['success' => true, 'member' => $updated->toArray()];
    }

    public function getUserMembership(int $userId, int $businessId): ?array
    {
        $member = $this->teamMemberRepo->findByUserAndBusiness($userId, $businessId);

        if (!$member || $member->status !== 'active') {
            return null;
        }

        return $member->toArray();
    }

    public function hasPermission(int $userId, int $businessId, string $permission): bool
    {
        if ($userId === $businessId) {
            return true;
        }

        $member = $this->teamMemberRepo->findByUserAndBusiness($userId, $businessId);

        if (!$member) {
            return false;
        }

        return $member->hasPermission($permission);
    }
}
