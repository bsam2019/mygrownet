<?php

namespace App\Http\Middleware;

use App\Domain\Workspace\Services\OrganizationAccessService;
use App\Domain\Workspace\ValueObjects\WorkspaceContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationAccess
{
    public function __construct(
        private OrganizationAccessService $orgAccess,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $context = $request->attributes->get('workspace_context');

        if ($context instanceof WorkspaceContext && $context->isOrganization()) {
            $user = $request->user();

            if (!$user) {
                abort(401);
            }

            $org = \App\Domain\Core\Models\Organization::find($context->organizationId);

            if (!$org || !$this->orgAccess->validateMembership($user, $org)) {
                abort(403, 'Not a member of this organization');
            }
        }

        return $next($request);
    }
}
