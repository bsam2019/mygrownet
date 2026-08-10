<?php

namespace App\Http\Middleware;

use App\Domain\Core\ValueObjects\PlatformContext;
use App\Domain\Workspace\Services\ApplicationAccessService;
use App\Domain\Workspace\Services\OrganizationAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPlatformContext
{
    public function __construct(
        private ApplicationAccessService $appAccess,
        private OrganizationAccessService $orgAccess,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (function_exists('inertia')) {
            $workspaceContext = $request->attributes->get('workspace_context');
            $platformContext = $request->attributes->get('platform_context');
            $user = $request->user();

            if ($user && $workspaceContext) {
                $autoLaunch = $request->attributes->get('auto_launch', false);

                inertia()->share('workspace', [
                    'context' => $workspaceContext->toArray(),
                    'platform_context' => $platformContext?->toArray(),
                    'auto_launch' => $autoLaunch,
                    'apps' => fn() => $this->appAccess->getAvailableApps($user, $workspaceContext),
                    'organizations' => function () use ($user) {
                        return $this->orgAccess->getAccessibleOrganizations($user)
                            ->map(fn($org) => [
                                'id' => $org->id,
                                'name' => $org->name,
                                'slug' => $org->slug,
                                'type' => $org->type,
                                'country' => $org->country,
                                'currency' => $org->currency,
                                'apps' => $org->installations
                                    ->where('status', 'active')
                                    ->filter(fn($inst) => $inst->application && in_array($inst->application->context_support, ['organization', 'both']) && $inst->application->type === 'business')
                                    ->map(fn($inst) => [
                                        'id' => $inst->application->id,
                                        'name' => $inst->application->name,
                                        'slug' => $inst->application->slug,
                                    ])->values(),
                            ]);
                    },
                ]);
            }
        }

        return $next($request);
    }
}
