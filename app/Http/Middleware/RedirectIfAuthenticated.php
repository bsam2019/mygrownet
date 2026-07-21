<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                if ($request->attributes->has('identity_gateway')) {
                    if ($returnUrl = session('identity_return_url')) {
                        return redirect()->away($returnUrl);
                    }
                    return redirect()->intended(route('workspace'));
                }

                return redirect('/dashboard');
            }
        }

        return $next($request);
    }
}
