<?php

namespace App\Http\Middleware;

use App\Domain\Workspace\Services\ContextResolverService;
use App\Domain\Workspace\Services\DomainResolverService;
use App\Domain\Workspace\Exceptions\DomainNotFoundException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveDomainContext
{
    public function __construct(
        private DomainResolverService $domainResolver,
        private ContextResolverService $contextResolver,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        try {
            $resolution = $this->domainResolver->resolve($host);
            $request->attributes->set('domain_resolution', $resolution);

            $context = $this->contextResolver->resolve(
                user: $request->user(),
                domainType: $resolution->type,
                orgHint: $resolution->organization,
            );
            $request->attributes->set('workspace_context', $context);
        } catch (DomainNotFoundException) {
            $request->attributes->set('domain_resolution', null);

            $context = $this->contextResolver->resolve(
                user: $request->user(),
                domainType: null,
            );
            $request->attributes->set('workspace_context', $context);
        }

        return $next($request);
    }
}
