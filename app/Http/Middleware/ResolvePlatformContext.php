<?php

namespace App\Http\Middleware;

use App\Domain\Core\ValueObjects\PlatformContext;
use App\Domain\Workspace\ValueObjects\WorkspaceContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolvePlatformContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $workspace = $request->attributes->get('workspace_context');
        $userId = $user ? (string) $user->id : '';

        $orgId = '';
        $appId = 'platform';
        $installationId = null;

        if ($workspace instanceof WorkspaceContext) {
            $orgId = $workspace->organizationId ? (string) $workspace->organizationId : '';
            $appId = $workspace->applicationId ? (string) $workspace->applicationId : 'platform';
        }

        $context = PlatformContext::make(
            userId: $userId,
            organizationId: $orgId,
            applicationId: $appId,
            installationId: $installationId,
            workspaceId: $orgId,
        );

        $request->attributes->set('platform_context', $context);

        app()->instance(PlatformContext::class, $context);

        return $next($request);
    }
}
