<?php

namespace App\Http\Controllers;

use App\Domain\Workspace\Services\ApplicationAccessService;
use App\Domain\Workspace\Services\ContextResolverService;
use App\Domain\Workspace\Services\OrganizationAccessService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkspaceController extends Controller
{
    public function __construct(
        private ApplicationAccessService $appAccess,
        private OrganizationAccessService $orgAccess,
        private ContextResolverService $contextResolver,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $context = $request->attributes->get('workspace_context');

        // If context is null, create a default personal context
        if (!$context) {
            $context = $this->contextResolver->resolve($user, null);
        }

        return Inertia::render('Workspace/Index', [
            'context' => $context?->toArray(),
            'apps' => $this->appAccess->getAvailableApps($user, $context),
            'organizations' => $this->orgAccess->getAccessibleOrganizations($user),
            'user' => $user->only('id', 'name', 'email'),
        ]);
    }

    public function switchContext(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:personal,organization',
            'organization_id' => 'required_if:type,organization|integer|exists:organizations,id',
        ]);

        $context = $this->contextResolver->switchContext(
            $request->user(),
            $validated['type'],
            $validated['organization_id'] ?? null,
        );

        return redirect()->route('workspace');
    }
}
