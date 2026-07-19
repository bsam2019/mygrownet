<?php

namespace App\Http\Middleware;

use App\Domain\Core\Models\Application;
use App\Domain\Workspace\Services\ApplicationAccessService;
use App\Domain\Workspace\ValueObjects\WorkspaceContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApplicationAccess
{
    public function __construct(
        private ApplicationAccessService $appAccess,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $context = $request->attributes->get('workspace_context');
        $user = $request->user();

        $resolution = $request->attributes->get('domain_resolution');
        $app = $resolution?->application;

        if ($app instanceof Application && $context instanceof WorkspaceContext && $user) {
            if ($app->requires_organization_context && $context->isPersonal()) {
                abort(403, 'This application requires an organization context');
            }

            if (!$this->appAccess->canAccess($user, $app, $context)) {
                abort(403, 'No access to this application');
            }
        }

        return $next($request);
    }
}
