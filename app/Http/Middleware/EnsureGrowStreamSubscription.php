<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\GrowStream\Services\AccessControlService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGrowStreamSubscription
{
    public function __construct(
        private AccessControlService $accessControl,
    ) {}

    /**
     * Guard routes that require a paid GrowStream subscription.
     *
     * @param  string  $accessLevel  'premium' (default) or 'free'
     */
    public function handle(Request $request, Closure $next, string $accessLevel = 'premium'): Response
    {
        if ($accessLevel === 'free') {
            return $next($request);
        }

        $user = $request->user();

        if (! $user || ! $this->accessControl->hasPaidSubscription($user)) {
            abort(403, 'A GrowStream subscription is required to access this content.');
        }

        return $next($request);
    }
}
