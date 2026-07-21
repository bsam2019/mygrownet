<?php

namespace App\Http\Controllers;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\ApplicationInstallation;
use App\Domain\Core\Models\Organization;
use App\Domain\Core\Models\OrganizationMember;
use App\Domain\Core\Services\OrganizationService;
use App\Domain\Workspace\Services\ApplicationAccessService;
use App\Domain\Workspace\Services\AppLaunchService;
use App\Domain\Workspace\Services\ContextResolverService;
use App\Domain\Workspace\Services\OrganizationAccessService;
use App\Domain\Workspace\ValueObjects\WorkspaceContext;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizBusinessProfileModel;
use App\Infrastructure\BizDocs\Persistence\Eloquent\BusinessProfileModel as BizDocsBusinessProfile;
use App\Models\QuickInvoice\QuickInvoiceProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrganizationWorkspaceController extends Controller
{
    public function __construct(
        private ApplicationAccessService $appAccess,
        private OrganizationAccessService $orgAccess,
        private ContextResolverService $contextResolver,
        private OrganizationService $orgService,
        private AppLaunchService $appLaunch,
    ) {}

    public function create(Request $request)
    {
        return Inertia::render('Workspace/CreateOrganization');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:3',
            'timezone' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:10',
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($validated, $user) {
            $org = $this->orgService->create([
                'name' => $validated['name'],
                'type' => $validated['type'] ?? 'general',
                'country' => $validated['country'] ?? null,
                'currency' => $validated['currency'] ?? null,
                'timezone' => $validated['timezone'] ?? null,
                'language' => $validated['language'] ?? null,
                'owner_id' => $user->id,
                'status' => 'active',
            ]);

            $this->orgService->addMember($org, $user, 'active');
            OrganizationMember::where('user_id', $user->id)
                ->where('organization_id', $org->id)
                ->update(['role' => 'admin']);

            $context = $this->contextResolver->switchContext($user, 'organization', $org->id);

            return redirect()->route('workspace.organization', ['slug' => $org->slug]);
        });
    }

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

        $activeInstallations = $org->installations->where('status', 'active');

        // Auto-launch if org has exactly one installed app
        if ($activeInstallations->count() === 1) {
            $singleApp = $activeInstallations->first()->application;
            if ($this->appAccess->canAccess($user, $singleApp, $context)) {
                return $this->appLaunch->launch($singleApp, $context, $user);
            }
        }

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
            'workspace' => [
                'context' => $context->toArray(),
                'apps' => $this->appAccess->getAvailableApps($user, $context),
                'organizations' => $this->orgAccess->getAccessibleOrganizations($user)
                    ->map(fn($o) => [
                        'id' => $o->id,
                        'name' => $o->name,
                        'slug' => $o->slug,
                        'type' => $o->type,
                        'country' => $o->country,
                        'currency' => $o->currency,
                        'apps' => $o->installations->map(fn($inst) => [
                            'id' => $inst->application->id,
                            'name' => $inst->application->name,
                            'slug' => $inst->application->slug,
                        ]),
                    ]),
            ],
            'apps' => $this->appAccess->getAvailableApps($user, $context),
            'members' => $this->orgAccess->getMembers($org),
            'context' => $context->toArray(),
            'userRole' => $userRole,
            'intendedApp' => $intendedApp,
        ]);
    }

    public function install(Request $request, string $slug, Application $application)
    {
        $org = Organization::where('slug', $slug)->firstOrFail();
        $user = $request->user();

        $this->orgAccess->validateMembership($user, $org);

        if ($application->lifecycle === 'retired' || !$application->is_active) {
            return redirect()->back()->with('error', 'This application is not available.');
        }

        DB::transaction(function () use ($org, $application, $user) {
            ApplicationInstallation::firstOrCreate(
                ['organization_id' => $org->id, 'application_id' => $application->id],
                ['status' => 'active']
            );

            $this->createStubIfNeeded($org, $application, $user);
        });

        return redirect()->route('workspace.organization', ['slug' => $org->slug])
            ->with('success', "{$application->name} installed successfully.");
    }

    private function createStubIfNeeded(Organization $org, Application $app, \App\Models\User $user): void
    {
        switch ($app->slug) {
            case 'bizboost':
                BizBoostBusinessModel::firstOrCreate(
                    ['organization_id' => $org->id],
                    ['name' => $org->name, 'email' => $user->email, 'status' => 'active']
                );
                break;
            case 'growbiz':
                GrowBizBusinessProfileModel::firstOrCreate(
                    ['organization_id' => $org->id],
                    ['business_name' => $org->name]
                );
                break;
            case 'bizdocs':
                BizDocsBusinessProfile::firstOrCreate(
                    ['organization_id' => $org->id],
                    ['business_name' => $org->name]
                );
                break;
            case 'quick-invoice':
                QuickInvoiceProfile::firstOrCreate(
                    ['organization_id' => $org->id],
                    ['name' => $org->name]
                );
                break;
        }
    }
}
