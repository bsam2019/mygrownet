<?php

namespace App\Domain\GrowFinance\Services;

use App\Domain\Module\Services\SubscriptionService;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceTeamMemberModel;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class TeamService
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Get team members for a business
     */
    public function getTeamMembers(int $businessId): array
    {
        return GrowFinanceTeamMemberModel::forBusiness($businessId)
            ->with('user:id,name,email')
            ->orderBy('role')
            ->orderBy('created_at')
            ->get()
            ->toArray();
    }

    /**
     * Check if user can add more team members
     */
    public function canAddTeamMember(User $owner): array
    {
        $limits = $this->subscriptionService->getUserLimits($owner);
        $maxMembers = $limits['team_members'] ?? 0;

        if ($maxMembers === 0) {
            return [
                'allowed' => false,
                'reason' => 'Multi-user access is available on Business plan. Please upgrade.',
            ];
        }

        $currentCount = GrowFinanceTeamMemberModel::forBusiness($owner->id)
            ->whereIn('status', [
                GrowFinanceTeamMemberModel::STATUS_ACTIVE,
                GrowFinanceTeamMemberModel::STATUS_PENDING,
            ])
            ->count();

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

    /**
     * Invite a team member
     */
    public function inviteTeamMember(
        int $businessId,
        string $email,
        string $role = GrowFinanceTeamMemberModel::ROLE_VIEWER,
        ?array $permissions = null
    ): array {
        // Check if user exists
        $user = User::where('email', $email)->first();

        if (!$user) {
            return [
                'success' => false,
                'error' => 'User not found. They must register on the platform first.',
            ];
        }

        // Check if already a member
        $existing = GrowFinanceTeamMemberModel::forBusiness($businessId)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return [
                'success' => false,
                'error' => 'This user is already a team member.',
            ];
        }

        // Create invitation
        $token = Str::random(32);

        $member = GrowFinanceTeamMemberModel::create([
            'business_id' => $businessId,
            'user_id' => $user->id,
            'role' => $role,
            'permissions' => $permissions,
            'status' => GrowFinanceTeamMemberModel::STATUS_PENDING,
            'invitation_token' => $token,
            'invited_at' => now(),
        ]);

        // TODO: Send invitation email
        // Mail::to($user->email)->send(new TeamInvitation($member, $token));

        return [
            'success' => true,
            'member' => $member,
            'token' => $token,
        ];
    }

    /**
     * Accept team invitation
     */
    public function acceptInvitation(string $token): array
    {
        $member = GrowFinanceTeamMemberModel::where('invitation_token', $token)
            ->where('status', GrowFinanceTeamMemberModel::STATUS_PENDING)
            ->first();

        if (!$member) {
            return [
                'success' => false,
                'error' => 'Invalid or expired invitation.',
            ];
        }

        $member->update([
            'status' => GrowFinanceTeamMemberModel::STATUS_ACTIVE,
            'invitation_token' => null,
            'accepted_at' => now(),
        ]);

        return [
            'success' => true,
            'member' => $member,
        ];
    }

    /**
     * Update team member role/permissions
     */
    public function updateTeamMember(
        int $businessId,
        int $memberId,
        string $role,
        ?array $permissions = null
    ): array {
        $member = GrowFinanceTeamMemberModel::forBusiness($businessId)
            ->findOrFail($memberId);

        // Cannot change owner role
        if ($member->role === GrowFinanceTeamMemberModel::ROLE_OWNER) {
            return [
                'success' => false,
                'error' => 'Cannot modify owner role.',
            ];
        }

        $member->update([
            'role' => $role,
            'permissions' => $permissions,
        ]);

        return [
            'success' => true,
            'member' => $member,
        ];
    }

    /**
     * Remove team member
     */
    public function removeTeamMember(int $businessId, int $memberId): array
    {
        $member = GrowFinanceTeamMemberModel::forBusiness($businessId)
            ->findOrFail($memberId);

        if ($member->role === GrowFinanceTeamMemberModel::ROLE_OWNER) {
            return [
                'success' => false,
                'error' => 'Cannot remove the business owner.',
            ];
        }

        $member->delete();

        return ['success' => true];
    }

    /**
     * Suspend team member
     */
    public function suspendTeamMember(int $businessId, int $memberId): array
    {
        $member = GrowFinanceTeamMemberModel::forBusiness($businessId)
            ->findOrFail($memberId);

        if ($member->role === GrowFinanceTeamMemberModel::ROLE_OWNER) {
            return [
                'success' => false,
                'error' => 'Cannot suspend the business owner.',
            ];
        }

        $member->update(['status' => GrowFinanceTeamMemberModel::STATUS_SUSPENDED]);

        return ['success' => true, 'member' => $member];
    }

    /**
     * Reactivate suspended team member
     */
    public function reactivateTeamMember(int $businessId, int $memberId): array
    {
        $member = GrowFinanceTeamMemberModel::forBusiness($businessId)
            ->where('status', GrowFinanceTeamMemberModel::STATUS_SUSPENDED)
            ->findOrFail($memberId);

        $member->update(['status' => GrowFinanceTeamMemberModel::STATUS_ACTIVE]);

        return ['success' => true, 'member' => $member];
    }

    /**
     * Get user's team membership for a business
     */
    public function getUserMembership(int $userId, int $businessId): ?GrowFinanceTeamMemberModel
    {
        return GrowFinanceTeamMemberModel::forBusiness($businessId)
            ->where('user_id', $userId)
            ->active()
            ->first();
    }

    /**
     * Check if user has permission in a business
     */
    public function hasPermission(int $userId, int $businessId, string $permission): bool
    {
        // Owner always has all permissions
        if ($userId === $businessId) {
            return true;
        }

        $membership = $this->getUserMembership($userId, $businessId);

        if (!$membership) {
            return false;
        }

        return $membership->hasPermission($permission);
    }
}
