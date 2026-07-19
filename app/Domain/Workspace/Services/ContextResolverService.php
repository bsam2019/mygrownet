<?php

namespace App\Domain\Workspace\Services;

use App\Domain\Core\Models\Organization;
use App\Domain\Core\Models\OrganizationMember;
use App\Models\User;
use App\Domain\Workspace\ValueObjects\WorkspaceContext;

class ContextResolverService
{
    private const SESSION_KEY = 'workspace_context';

    public function resolve(?User $user, ?string $domainType, ?Organization $orgHint = null): WorkspaceContext
    {
        $context = new WorkspaceContext();

        if (!$user) {
            return $context->setGuest();
        }

        if ($cached = session(self::SESSION_KEY)) {
            return WorkspaceContext::fromArray($cached);
        }

        if ($domainType === 'organization' && $orgHint) {
            return $context->setOrganization($orgHint);
        }

        return $this->resolveDefault($user);
    }

    public function switchContext(User $user, string $type, ?int $organizationId = null): WorkspaceContext
    {
        $context = new WorkspaceContext();

        if ($type === 'organization') {
            OrganizationMember::where('user_id', $user->id)
                ->where('organization_id', $organizationId)
                ->where('status', 'active')
                ->firstOrFail();

            $org = Organization::findOrFail($organizationId);
            $context->setOrganization($org);
        } else {
            $context->setPersonal();
        }

        session([self::SESSION_KEY => $context->toArray()]);

        return $context;
    }

    public function clearContext(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    protected function resolveDefault(User $user): WorkspaceContext
    {
        $preference = $user->profile?->default_workspace_context;

        if ($preference === 'organization') {
            $org = OrganizationMember::where('user_id', $user->id)
                ->where('status', 'active')
                ->first()?->organization;

            if ($org) {
                return (new WorkspaceContext())->setOrganization($org);
            }
        }

        return (new WorkspaceContext())->setPersonal();
    }
}
