<?php

namespace App\Http\Controllers;

use App\Domain\Core\Models\Organization;
use App\Domain\Workspace\Services\ApplicationAccessService;
use App\Domain\Workspace\Services\ContextResolverService;
use App\Domain\Workspace\Services\OrganizationAccessService;
use App\Domain\Workspace\ValueObjects\WorkspaceContext;
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

        $org->loadMissing('installations.application');

        $userRole = $this->orgAccess->getUserRole($user, $org);

        activity('workspace')
            ->performedOn($org)
            ->causedBy($user)
            ->withProperties([
                'organization_slug' => $slug,
                'user_role' => $userRole,
            ])
            ->log('organization_workspace_viewed');

        $intendedApp = null;
        $domainResolution = $request->attributes->get('domain_resolution');
        if ($domainResolution && $domainResolution->application) {
            $intendedApp = [
                'id' => $domainResolution->application->id,
                'name' => $domainResolution->application->name,
                'slug' => $domainResolution->application->slug,
                'url' => $domainResolution->application->url,
            ];
        }

        return Inertia::render('Workspace/Organization', [
            'organization' => [
                'id' => $org->id,
                'name' => $org->name,
                'slug' => $org->slug,
                'type' => $org->type,
                'country' => $org->country,
                'currency' => $org->currency,
                'timezone' => $org->timezone,
                'language' => $org->language,
                'status' => $org->status,
                'installed_apps' => $org->installations
                    ->where('status', 'active')
                    ->values()
                    ->map(fn($inst) => [
                        'id' => $inst->application->id,
                        'name' => $inst->application->name,
                        'slug' => $inst->application->slug,
                        'url' => $inst->application->url,
                        'category' => $inst->application->category,
                    ]),
            ],
            'apps' => $this->appAccess->getAvailableApps($user, $context),
            'members' => $this->orgAccess->getMembers($org),
            'context' => $context->toArray(),
            'userRole' => $userRole,
            'intendedApp' => $intendedApp,
        ]);
    }
}
