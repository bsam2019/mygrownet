<?php

namespace App\Http\Middleware;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Infrastructure\GrowBuilder\Models\SiteUser;
use App\Infrastructure\GrowBuilder\Models\SiteUserSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SiteUserAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $request->route('subdomain');
        
        if (!$subdomain) {
            return redirect()->route('home');
        }

        // Find the site
        $site = GrowBuilderSite::where('subdomain', $subdomain)->first();
        
        if (!$site) {
            abort(404, 'Site not found');
        }

        // Check for site user session
        $sessionToken = $request->cookie('site_user_token_' . $site->id) 
            ?? $request->bearerToken();

        if (!$sessionToken) {
            return $this->redirectToLogin($subdomain);
        }

        // Find valid session
        $session = SiteUserSession::where('token', $sessionToken)
            ->active()
            ->first();

        if (!$session) {
            return $this->redirectToLogin($subdomain);
        }

        // Get the user
        $user = $session->user;

        if (!$user || !$user->isActive()) {
            $session->delete();
            return $this->redirectToLogin($subdomain);
        }

        // Update session activity
        $session->touch();

        // Share site and user with the request
        $request->attributes->set('site', $site);
        $request->attributes->set('site_user', $user);
        $request->attributes->set('site_user_session', $session);

        // Share with views
        view()->share('site', $site);
        view()->share('siteUser', $user);

        return $next($request);
    }

    protected function redirectToLogin(string $subdomain): Response
    {
        return redirect()->route('site.login', ['subdomain' => $subdomain]);
    }
}
