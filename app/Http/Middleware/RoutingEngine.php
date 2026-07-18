<?php

namespace App\Http\Middleware;

use App\Domain\Core\Services\WorkspaceResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoutingEngine
{
    public function __construct(
        private WorkspaceResolver $resolver,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        $workspace = $this->resolver->resolve($host);

        if ($workspace) {
            $request->attributes->set('_platform_workspace', $workspace);

            Log::debug('RoutingEngine resolved workspace', [
                'host' => $host,
                'type' => $workspace->type,
                'application' => $workspace->application?->slug,
                'organization' => $workspace->organization?->slug,
                'domain' => $workspace->domain?->domain,
                'path' => $request->path(),
            ]);
        } else {
            Log::debug('RoutingEngine no workspace resolved', [
                'host' => $host,
                'path' => $request->path(),
            ]);
        }

        return $next($request);
    }
}
