<?php

namespace App\Http\Middleware;

use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\Subdomain;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
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
     * Detect if request is for a GrowBuilder subdomain or custom domain and route accordingly.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        
        \Log::info('DetectSubdomain: Host = ' . $host);
        
        // First, check if this is a custom domain
        $customDomainSite = $this->findSiteByCustomDomain($host);
        if ($customDomainSite) {
            return $this->renderSite($request, $customDomainSite, $host, true);
        }
        
        // Check if this is a subdomain request (including www.subdomain.mygrownet.com)
        if (preg_match('/^(?:www\.)?([a-z0-9-]+)\.mygrownet\.com$/i', $host, $matches)) {
            \Log::info('DetectSubdomain: Matched subdomain = ' . $matches[1]);
            $subdomain = strtolower($matches[1]);
            
            // Skip main domain variations (but not www.subdomain)
            if ($subdomain === 'mygrownet') {
                return $next($request);
            }
            
            // If it's just www.mygrownet.com (no subdomain), skip
            if ($subdomain === 'www' && $host === 'www.mygrownet.com') {
                return $next($request);
            }
            
            // Handle geopamu subdomain - dispatch directly to controller
            if ($subdomain === 'geopamu') {
                return $this->handleGeopamuSubdomain($request);
            }
            
            // Handle wowthem subdomain - dispatch directly to controller
            if ($subdomain === 'wowthem') {
                return $this->handleWowthemSubdomain($request);
            }
            
            // Skip other reserved subdomains
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
                \Log::info('DetectSubdomain: Site found = ' . ($site ? 'yes' : 'no') . ', published = ' . ($site && $site->isPublished() ? 'yes' : 'no'));
                
                if ($site) {
                    // Handle manifest.json request
                    if ($request->path() === 'manifest.json') {
                        return $this->handleManifest($request, $site);
                    }
                    
                    // Handle auth routes (login, register, etc.)
                    if ($this->isAuthRoute($request->path())) {
                        return $this->handleAuthRoute($request, $subdomain);
                    }
                    
                    // Only render site if published
                    if ($site->isPublished()) {
                        try {
                            return $this->renderSite($request, $site, "https://{$subdomain}.mygrownet.com", false);
                        } catch (\Exception $renderException) {
                            \Log::error('DetectSubdomain: Render exception - ' . $renderException->getMessage());
                            \Log::error('DetectSubdomain: Stack trace - ' . $renderException->getTraceAsString());
                            // Re-throw to show error instead of falling back to main site
                            throw $renderException;
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('DetectSubdomain: Exception - ' . $e->getMessage());
                \Log::error('DetectSubdomain: Stack trace - ' . $e->getTraceAsString());
                // If it's a render exception, re-throw it
                if (isset($renderException)) {
                    throw $e;
                }
                // Site not found or error, continue to main site
            }
        }

        return $next($request);
    }
    
    /**
     * Handle Geopamu subdomain requests
     */
    private function handleGeopamuSubdomain(Request $request): Response
    {
        $path = $request->path();
        $controller = app()->make(\App\Http\Controllers\GeopamuController::class);
        
        // Map paths to controller methods
        $result = match(true) {
            $path === '/' => $controller->home(),
            str_starts_with($path, 'services') => $controller->services(),
            str_starts_with($path, 'portfolio') => $controller->portfolio(),
            str_starts_with($path, 'about') => $controller->about(),
            str_starts_with($path, 'contact') => $controller->contact(),
            str_starts_with($path, 'blog') => $this->handleGeopamuBlog($request, $path),
            default => $controller->home() // Fallback to home
        };
        
        // Handle Inertia Response properly
        if ($result instanceof \Inertia\Response) {
            return $result->toResponse($request);
        }
        
        return $result instanceof Response ? $result : response($result);
    }
    
    /**
     * Handle Geopamu blog routes
     */
    private function handleGeopamuBlog(Request $request, string $path): mixed
    {
        $blogController = app()->make(\App\Http\Controllers\Geopamu\BlogController::class);
        
        // Extract slug if present
        if (preg_match('#^blog/([^/]+)$#', $path, $matches)) {
            return $blogController->show($matches[1]);
        }
        
        return $blogController->index();
    }
    
    /**
     * Handle WowThem subdomain requests
     */
    private function handleWowthemSubdomain(Request $request): Response
    {
        $path = $request->path();
        $controller = app()->make(\App\Http\Controllers\Wedding\WeddingController::class);
        
        // Map paths to controller methods
        $result = match(true) {
            $path === '/' => $controller->landingPage(),
            preg_match('#^templates/([^/]+)/preview$#', $path) => $controller->previewTemplate($request->route('slug') ?? ''),
            default => $controller->landingPage() // Fallback to landing page
        };
        
        // Handle Inertia Response properly
        if ($result instanceof \Inertia\Response) {
            return $result->toResponse($request);
        }
        
        return $result instanceof Response ? $result : response($result);
    }
    
    /**
     * Find a site by custom domain
     */
    private function findSiteByCustomDomain(string $host): ?object
    {
        // Remove www. prefix if present
        $domain = preg_replace('/^www\./i', '', $host);
        
        // Skip mygrownet.com domains
        if (str_ends_with($domain, 'mygrownet.com')) {
            return null;
        }
        
        $siteModel = GrowBuilderSite::where('custom_domain', $domain)
            ->orWhere('custom_domain', 'www.' . $domain)
            ->where('status', 'published')
            ->first();
            
        if (!$siteModel) {
            return null;
        }
        
        // Convert to domain entity
        try {
            return $this->siteRepository->findBySubdomain(Subdomain::fromString($siteModel->subdomain));
        } catch (\Exception $e) {
            \Log::error('DetectSubdomain: Custom domain lookup failed - ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Handle manifest.json request for GrowBuilder site
     */
    private function handleManifest(Request $request, object $site): Response
    {
        $result = app()->make(\App\Http\Controllers\GrowBuilder\ManifestController::class)
            ->manifest($request, $site->getSubdomain()->value());
        
        return $result instanceof Response ? $result : response($result);
    }
    
    /**
     * Check if the path is an auth route
     */
    private function isAuthRoute(string $path): bool
    {
        $authPaths = ['login', 'register', 'forgot-password', 'reset-password', 'logout'];
        
        foreach ($authPaths as $authPath) {
            if ($path === $authPath || str_starts_with($path, $authPath . '/')) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Handle auth routes for subdomain
     */
    private function handleAuthRoute(Request $request, string $subdomain): Response
    {
        $path = $request->path();
        $method = $request->method();
        
        $authController = app()->make(\App\Http\Controllers\GrowBuilder\SiteAuthController::class);
        
        // Map paths to controller methods
        $result = match(true) {
            $path === 'login' && $method === 'GET' => $authController->showLogin($subdomain),
            $path === 'login' && $method === 'POST' => $authController->login($request, $subdomain),
            $path === 'register' && $method === 'GET' => $authController->showRegister($subdomain),
            $path === 'register' && $method === 'POST' => $authController->register($request, $subdomain),
            $path === 'logout' && $method === 'POST' => $authController->logout($request, $subdomain),
            str_starts_with($path, 'forgot-password') && $method === 'GET' => $authController->showForgotPassword($subdomain),
            str_starts_with($path, 'forgot-password') && $method === 'POST' => $authController->sendResetLink($request, $subdomain),
            preg_match('#^reset-password/([^/]+)$#', $path, $matches) && $method === 'GET' => $authController->showResetPassword($subdomain, $matches[1]),
            $path === 'reset-password' && $method === 'POST' => $authController->resetPassword($request, $subdomain),
            default => abort(404)
        };
        
        // Handle Inertia Response properly
        if ($result instanceof \Inertia\Response) {
            return $result->toResponse($request);
        }
        
        return $result instanceof Response ? $result : response($result);
    }
    
    /**
     * Render the site using the RenderController
     */
    private function renderSite(Request $request, object $site, string $baseUrl, bool $isCustomDomain): Response
    {
        // Set the asset URL for Vite assets
        URL::forceRootUrl($baseUrl);
        config(['app.url' => $baseUrl]);
        
        // CRITICAL: Force asset URL for Vite to use subdomain
        config(['app.asset_url' => $baseUrl]);
        
        // Forward to subdomain route handler
        $path = $request->path();
        $path = $path === '/' ? '' : $path;
        
        // Dispatch to the subdomain render controller
        $result = app()->make(\App\Http\Controllers\GrowBuilder\RenderController::class)
            ->render($request, $site->getSubdomain()->value(), $path ?: null);
        
        // Handle Inertia Response properly
        if ($result instanceof \Inertia\Response) {
            return $result->toResponse($request);
        }
        
        // Ensure we return a Response, not a View
        return $result instanceof Response ? $result : response($result);
    }
}
