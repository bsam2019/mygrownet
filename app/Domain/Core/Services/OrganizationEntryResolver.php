<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Contracts\CompanyDetailsProvider;
use App\Domain\Workspace\Services\ContextResolverService;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Decoupled helper that lets an application entry point resolve which
 * organization the logged-in user is currently acting as, and obtain that
 * organization's canonical company details.
 *
 * This is the boundary shim for "org-context-first" entry: the app keeps
 * owning its own per-app profile, but it learns its tenant (business/company)
 * from the platform's active workspace context instead of asking the user to
 * re-enter company details. Apps fall back to their existing per-user or
 * per-session resolution when no organization is active.
 */
class OrganizationEntryResolver
{
    public function __construct(
        private ContextResolverService $contextResolver,
        private CompanyDetailsProvider $companyDetails,
    ) {}

    /**
     * The active organization id from the resolved workspace context, if any.
     */
    public function activeOrganizationId(?Authenticatable $user): ?int
    {
        if (!$user) {
            return null;
        }

        $context = $this->contextResolver->resolve(
            user: $user,
            domainType: null,
        );

        return $context->organizationId;
    }

    /**
     * Canonical company details for the active organization, or an empty array.
     *
     * @return array<string, mixed>
     */
    public function companyDetails(?Authenticatable $user): array
    {
        $orgId = $this->activeOrganizationId($user);
        if ($orgId === null || !$this->companyDetails->hasCompanyDetails($orgId)) {
            return [];
        }

        return $this->companyDetails->getCompanyDetails($orgId);
    }
}