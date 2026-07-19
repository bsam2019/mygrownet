<?php

namespace App\Http\Controllers;

use App\Domain\Core\Models\Organization;
use App\Domain\Workspace\Services\ApplicationAccessService;
use App\Domain\Workspace\Services\ContextResolverService;
use App\Domain\Workspace\Services\OrganizationAccessService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrganizationWorkspaceController extends Controller
{
    public function __construct(
        private ApplicationAccessService $appAccess,
        private OrganizationAccessService $orgAccess,
        private ContextResolverService $contextResolver,
    ) {}

    public function show(Request $request, string $slug)
    {
        $org = Organization::where('slug', $slug)->firstOrFail();
        $user = $request->user();

        $this->orgAccess->validateMembership($user, $org);

        $context = $this->contextResolver->switchContext(
            $user, 'organization', $org->id,
        );

        return Inertia::render('Workspace/Organization', [
            'organization' => $org,
            'apps' => $this->appAccess->getAvailableApps($user, $context),
            'members' => $this->orgAccess->getMembers($org),
            'context' => $context->toArray(),
        ]);
    }
}
