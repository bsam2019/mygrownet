<?php

namespace App\Http\Controllers;

use App\Domain\Core\Models\Application;
use App\Domain\Workspace\Services\ApplicationAccessService;
use App\Domain\Workspace\Services\AppLaunchService;
use App\Domain\Workspace\Services\ContextResolverService;
use App\Domain\Workspace\Services\OrganizationAccessService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkspaceController extends Controller
{
    public function __construct(
        private ApplicationAccessService $appAccess,
        private AppLaunchService $appLaunch,
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
            'organizations' => $this->orgAccess->getAccessibleOrganizations($user)
                ->map(fn($org) => [
                    'id' => $org->id,
                    'name' => $org->name,
                    'slug' => $org->slug,
                    'type' => $org->type,
                    'country' => $org->country,
                    'currency' => $org->currency,
                    'apps' => $org->installations->map(fn($inst) => [
                        'id' => $inst->application->id,
                        'name' => $inst->application->name,
                        'slug' => $inst->application->slug,
                    ]),
                ]),
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

        activity('workspace')
            ->performedOn($context->isOrganization() ? \App\Domain\Core\Models\Organization::find($context->organizationId) : null)
            ->causedBy($request->user())
            ->withProperties(['context_type' => $validated['type'], 'organization_id' => $validated['organization_id'] ?? null])
            ->log('context_switch');

        return redirect()->route('workspace');
    }

    public function launch(Request $request, Application $application)
    {
        $user = $request->user();
        $context = $request->attributes->get('workspace_context');

        if (!$context) {
            $context = $this->contextResolver->resolve($user, null);
        }

        if (!$this->appAccess->canAccess($user, $application, $context)) {
            abort(403, 'No access to this application');
        }

        activity('workspace')
            ->performedOn($application)
            ->causedBy($user)
            ->withProperties([
                'context_type' => $context->type,
                'organization_id' => $context->organizationId,
                'application_slug' => $application->slug,
            ])
            ->log('app_launch');

        return $this->appLaunch->launch($application, $context, $user);
    }
}
