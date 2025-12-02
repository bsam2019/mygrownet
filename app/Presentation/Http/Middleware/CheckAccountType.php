<?php

namespace App\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountType
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$accountTypes): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has any of the required account types
        $userAccountTypes = is_array($user->account_types) 
            ? $user->account_types 
            : [$user->account_type];

        $hasAccess = !empty(array_intersect($accountTypes, $userAccountTypes));

        if (!$hasAccess) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Your account type does not have access to this feature.');
        }

        return $next($request);
    }
}
