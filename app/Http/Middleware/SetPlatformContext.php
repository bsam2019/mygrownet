<?php

namespace App\Http\Middleware;

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
            $context = $request->attributes->get('workspace_context');
            $user = $request->user();

            if ($user && $context) {
                inertia()->share('workspace', [
                    'context' => $context->toArray(),
                    'apps' => fn() => $this->appAccess->getAvailableApps($user, $context),
                    'organizations' => fn() => $this->orgAccess->getAccessibleOrganizations($user),
                ]);
            }
        }

        return $next($request);
    }
}
