<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use Symfony\Component\HttpFoundation\Response;

class SubdomainCheck
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $request->route('subdomain');

        // Redirect www to main domain
        if ($subdomain === 'www') {
            return redirect()->to('https://mygrownet.com' . $request->getRequestUri(), 301);
        }

        // Block reserved subdomains
        $reserved = [
            'api', 'admin', 'mail', 'ftp', 'smtp', 'pop', 'imap', 
            'webmail', 'cpanel', 'whm', 'ns1', 'ns2', 'mx', 'email',
        ];
        
        if (in_array(strtolower($subdomain), $reserved)) {
            abort(404, 'This subdomain is reserved.');
        }

        // Note: GrowBuilder sites are handled by DetectSubdomain middleware
        // This middleware only handles non-GrowBuilder subdomains

        // Set the asset URL to the current subdomain to avoid CORS issues
        $currentUrl = "https://{$subdomain}.mygrownet.com";
        URL::forceRootUrl($currentUrl);
        config(['app.url' => $currentUrl]);
        config(['app.asset_url' => $currentUrl]);

        return $next($request);
    }
}
