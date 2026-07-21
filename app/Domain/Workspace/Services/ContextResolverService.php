<?php

namespace App\Domain\Workspace\Services;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\Organization;
use App\Domain\Core\Models\OrganizationMember;
use App\Domain\Workspace\ValueObjects\DomainResolution;
use App\Models\User;
use App\Domain\Workspace\ValueObjects\WorkspaceContext;

class ContextResolverService
{
    private const SESSION_KEY = 'workspace_context';

    public function resolve(?User $user, ?string $domainType, ?Organization $orgHint = null, ?DomainResolution $resolution = null): WorkspaceContext
    {
        $context = new WorkspaceContext();

        if (!$user) {
            return $context->setGuest();
        }

        if ($cached = session(self::SESSION_KEY)) {
            $context = WorkspaceContext::fromArray($cached);
        } elseif ($domainType === 'organization' && $orgHint) {
            $context->setOrganization($orgHint);
        } else {
            $context = $this->resolveDefault($user);
        }

        if ($resolution?->application) {
            $context->applicationId = $resolution->application->id;
        }

        return $context;
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
        $preference = session('workspace_preference', 'personal');

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
