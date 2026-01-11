<?php

namespace App\Http\Middleware;

use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\Subdomain;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class DetectSubdomain
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository
    ) {}

    /**
     * Handle an incoming request.
     * Detect if request is for a GrowBuilder subdomain and route accordingly.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        
        // Check if this is a subdomain request
        if (preg_match('/^([a-z0-9-]+)\.mygrownet\.com$/i', $host, $matches)) {
            $subdomain = strtolower($matches[1]);
            
            // Skip main domain variations
            if (in_array($subdomain, ['www', 'mygrownet'])) {
                return $next($request);
            }
            
            // Skip reserved subdomains
            $reserved = [
                'api', 'admin', 'mail', 'ftp', 'smtp', 'pop', 'imap', 
                'webmail', 'cpanel', 'whm', 'ns1', 'ns2', 'mx', 'email',
                'growbuilder', 'app', 'dashboard', 'portal', 'staging', 'dev'
            ];
            
            if (in_array($subdomain, $reserved)) {
                return $next($request);
            }
            
            // Check if site exists
            try {
                $site = $this->siteRepository->findBySubdomain(Subdomain::fromString($subdomain));
                
                if ($site && $site->isPublished()) {
                    // Set the asset URL to the current subdomain to avoid CORS issues
                    $currentUrl = "https://{$subdomain}.mygrownet.com";
                    URL::forceRootUrl($currentUrl);
                    config(['app.url' => $currentUrl]);
                    
                    // Forward to subdomain route handler
                    $path = $request->path();
                    $path = $path === '/' ? '' : $path;
                    
                    // Create new request with subdomain parameter
                    $request->route()->setParameter('subdomain', $subdomain);
                    
                    // Dispatch to the subdomain render controller
                    return app()->make(\App\Http\Controllers\GrowBuilder\RenderController::class)
                        ->render($request, $subdomain, $path ?: null);
                }
            } catch (\Exception $e) {
                // Site not found, continue to main site
            }
        }

        return $next($request);
    }
}
