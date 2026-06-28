<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\AccountType;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$types  Account types allowed (e.g., 'member', 'client')
     */
    public function handle(Request $request, Closure $next, string ...$types): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Convert string types to AccountType enums
        $allowedTypes = [];
        foreach ($types as $type) {
            try {
                $allowedTypes[] = AccountType::from($type);
            } catch (\ValueError $e) {
                // Skip invalid account type
                continue;
            }
        }

        // Check if user has any of the allowed account types
        foreach ($allowedTypes as $type) {
            if ($request->user()->hasAccountType($type)) {
                return $next($request);
            }
        }

        // User doesn't have required account type
        abort(403, 'You do not have access to this area. Required account type: ' . implode(' or ', $types));
    }
}
