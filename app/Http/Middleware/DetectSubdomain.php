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
        // Try multiple headers for the host (Cloudflare may use different ones)
        $host = $request->header('X-Forwarded-Host') 
            ?? $request->header('X-Original-Host')
            ?? $request->header('Host')
            ?? $request->getHost();
        
        \Log::info('DetectSubdomain: Host = ' . $host);
        \Log::info('DetectSubdomain: All headers = ' . json_encode([
            'Host' => $request->header('Host'),
            'X-Forwarded-Host' => $request->header('X-Forwarded-Host'),
            'X-Original-Host' => $request->header('X-Original-Host'),
            'CF-Connecting-IP' => $request->header('CF-Connecting-IP'),
            'getHost()' => $request->getHost(),
        ]));
        
        // Check if this is a subdomain request
        if (preg_match('/^([a-z0-9-]+)\.mygrownet\.com$/i', $host, $matches)) {
            \Log::info('DetectSubdomain: Regex matched');
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
                \Log::info('DetectSubdomain: Looking for site with subdomain: ' . $subdomain);
                $site = $this->siteRepository->findBySubdomain(Subdomain::fromString($subdomain));
                
                \Log::info('DetectSubdomain: Site found = ' . ($site ? 'yes (id=' . $site->getId()->value() . ')' : 'no'));
                \Log::info('DetectSubdomain: Site published = ' . ($site && $site->isPublished() ? 'yes' : 'no'));
                
                if ($site && $site->isPublished()) {
                    // Set the asset URL to the current subdomain to avoid CORS issues
                    $currentUrl = "https://{$subdomain}.mygrownet.com";
                    URL::forceRootUrl($currentUrl);
                    config(['app.url' => $currentUrl]);
                    
                    // Forward to subdomain route handler
                    $path = $request->path();
                    $path = $path === '/' ? '' : $path;
                    
                    // Dispatch to the subdomain render controller
                    $result = app()->make(\App\Http\Controllers\GrowBuilder\RenderController::class)
                        ->render($request, $subdomain, $path ?: null);
                    
                    // Ensure we return a Response, not a View
                    return $result instanceof Response ? $result : response($result);
                }
            } catch (\Exception $e) {
                \Log::error('DetectSubdomain: Exception - ' . $e->getMessage());
                // Site not found or error, continue to main site
            }
        } else {
            \Log::info('DetectSubdomain: Regex did NOT match for host: ' . $host);
        }

        return $next($request);
    }
}
