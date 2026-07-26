<?php

namespace App\Http\Middleware;

use App\Domain\Core\Services\PlatformContextResolver;
use App\Domain\Core\ValueObjects\PlatformContext;

class RestoreTenantContext
{
    public function __construct(
        private PlatformContextResolver $contextResolver,
    ) {}

    public function handle(object $job, \Closure $next): mixed
    {
        $contextData = $job->platformContext ?? null;

        if ($contextData) {
            $context = PlatformContext::fromArray($contextData);
            $this->contextResolver->setContext($context);
        }

        return $next($job);
    }
}
