<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Domain\GrowBuilder\Repositories\PageRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\Subdomain;
use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPaymentSettings;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderProduct;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RenderController extends Controller
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
        private PageRepositoryInterface $pageRepository,
    ) {}

    public function render(Request $request, string $subdomain, ?string $slug = null)
    {
        // Debug logging
        file_put_contents(storage_path('logs/custom-domain-debug.log'), 
            date('Y-m-d H:i:s') . " - RenderController::render - Subdomain: {$subdomain}, Slug: {$slug}\n", 
            FILE_APPEND
        );
        
        try {
            $site = $this->siteRepository->findBySubdomain(Subdomain::fromString($subdomain));
        } catch (\Exception $e) {
            file_put_contents(storage_path('logs/custom-domain-debug.log'), 
                date('Y-m-d H:i:s') . " - RenderController: Exception finding site - " . $e->getMessage() . "\n", 
                FILE_APPEND
            );
            abort(404);
        }

        if (!$site || !$site->isPublished()) {
            file_put_contents(storage_path('logs/custom-domain-debug.log'), 
                date('Y-m-d H:i:s') . " - RenderController: Site not found or not published\n", 
                FILE_APPEND
            );
            abort(404);
        }

        // Handle checkout page
        if ($slug === 'checkout') {
            return $this->renderCheckout($site);
        }

        // Get page
        if ($slug) {
            $page = $this->pageRepository->findBySiteIdAndSlug($site->getId(), $slug);
            file_put_contents(storage_path('logs/custom-domain-debug.log'), 
                date('Y-m-d H:i:s') . " - RenderController: Looking for page with slug '{$slug}' - Found: " . ($page ? 'yes' : 'no') . "\n", 
                FILE_APPEND
            );
        } else {
            $page = $this->pageRepository->findHomepage($site->getId());
            file_put_contents(storage_path('logs/custom-domain-debug.log'), 
                date('Y-m-d H:i:s') . " - RenderController: Looking for homepage - Found: " . ($page ? 'yes' : 'no') . "\n", 
                FILE_APPEND
            );
        }

        if (!$page || !$page->isPublished()) {
            file_put_contents(storage_path('logs/custom-domain-debug.log'), 
                date('Y-m-d H:i:s') . " - RenderController: Page not found or not published - Returning 404\n", 
                FILE_APPEND
            );
            abort(404);
        }

        // Track page view
        $this->trackPageView($request, $site, $page);

        // Get the Eloquent model for additional data
        $siteModel = GrowBuilderSite::find($site->getId()->value());

        // Get navigation pages
        $navPages = $siteModel->pages()
            ->where('is_published', true)
            ->where('show_in_nav', true)
            ->orderBy('nav_order')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'isHomepage' => $p->is_homepage,
            ]);

        // Get page model
        $pageModel = $siteModel->pages()->where('slug', $slug ?? '')->first() 
            ?? $siteModel->pages()->where('is_homepage', true)->first();

        // Get products if e-commerce is enabled
        $products = GrowBuilderProduct::where('site_id', $site->getId()->value())
            ->where('is_active', true)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $p->price,
                'priceFormatted' => $p->formatted_price,
                'comparePrice' => $p->compare_price,
                'comparePriceFormatted' => $p->compare_price ? 'K' . number_format($p->compare_price / 100, 2) : null,
                'image' => $p->main_image,
                'images' => $p->images,
                'shortDescription' => $p->short_description,
                'category' => $p->category,
                'inStock' => $p->isInStock(),
                'isFeatured' => $p->is_featured,
                'hasDiscount' => $p->hasDiscount(),
                'discountPercentage' => $p->discount_percentage,
            ]);

        // Use Inertia to render the same Vue component as preview
        return Inertia::render('GrowBuilder/Preview/Site', [
            'site' => [
                'id' => $site->getId()->value(),
                'name' => $site->getName(),
                'subdomain' => $site->getSubdomain()->value(),
                'theme' => $site->getTheme()?->toArray() ?? [],
                'logo' => $site->getLogo(),
                'favicon' => $site->getFavicon(),
                'url' => config('app.url'),
            ],
            'page' => [
                'id' => $pageModel->id,
                'title' => $pageModel->title,
                'slug' => $pageModel->slug,
                'content' => $pageModel->content_json,
                'isHomepage' => $pageModel->is_homepage,
            ],
            'pages' => $navPages,
            'settings' => $siteModel->settings,
            'products' => $products,
            'isPreview' => false,
            'showWatermark' => false, // Don't show watermark on live subdomain
        ]);
    }

    private function renderCheckout($site)
    {
        $siteModel = GrowBuilderSite::find($site->getId()->value());
        $paymentSettings = GrowBuilderPaymentSettings::where('site_id', $site->getId()->value())->first();

        return Inertia::render('GrowBuilder/Preview/Checkout', [
            'site' => [
                'id' => $site->getId()->value(),
                'name' => $site->getName(),
                'subdomain' => $site->getSubdomain()->value(),
                'theme' => $site->getTheme()?->toArray() ?? [],
                'logo' => $site->getLogo(),
            ],
            'settings' => $siteModel->settings,
            'paymentSettings' => $paymentSettings ? $paymentSettings->toArray() : [
                'momo_enabled' => false,
                'airtel_enabled' => false,
                'cod_enabled' => true,
                'bank_enabled' => false,
            ],
        ]);
    }

    private function trackPageView(Request $request, $site, $page): void
    {
        try {
            GrowBuilderPageView::create([
                'site_id' => $site->getId()->value(),
                'page_id' => $page->getId()->value(),
                'path' => $request->path(),
                'referrer' => $request->header('referer'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_type' => $this->detectDeviceType($request->userAgent()),
                'viewed_date' => now()->toDateString(),
            ]);
        } catch (\Exception $e) {
            // Silently fail - don't break page render for analytics
        }
    }

    private function detectDeviceType(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android')) {
            return 'mobile';
        }

        if (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Generate sitemap.xml for the site
     */
    public function sitemap(Request $request, string $subdomain)
    {
        try {
            $site = $this->siteRepository->findBySubdomain(Subdomain::fromString($subdomain));
        } catch (\Exception $e) {
            abort(404);
        }

        if (!$site || !$site->isPublished()) {
            abort(404);
        }

        $baseUrl = config('app.url');
        $pages = $this->pageRepository->findPublishedBySiteId($site->getId());

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($pages as $page) {
            $url = $page->isHomepage() ? $baseUrl : "{$baseUrl}/{$page->getSlug()}";
            $lastmod = $page->getUpdatedAt()?->format('Y-m-d') ?? now()->format('Y-m-d');
            $priority = $page->isHomepage() ? '1.0' : '0.8';

            $xml .= "  <url>\n";
            $xml .= "    <loc>{$url}</loc>\n";
            $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>{$priority}</priority>\n";
            $xml .= "  </url>\n";
        }

        // Add blog posts if any
        $blogPosts = \App\Infrastructure\GrowBuilder\Models\SiteBlogPost::where('site_id', $site->getId()->value())
            ->where('status', 'published')
            ->get();

        foreach ($blogPosts as $post) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$baseUrl}/blog/{$post->slug}</loc>\n";
            $xml .= "    <lastmod>{$post->updated_at->format('Y-m-d')}</lastmod>\n";
            $xml .= "    <changefreq>monthly</changefreq>\n";
            $xml .= "    <priority>0.6</priority>\n";
            $xml .= "  </url>\n";
        }

        // Add products if any
        $products = \App\Infrastructure\GrowBuilder\Models\GrowBuilderProduct::where('site_id', $site->getId()->value())
            ->where('is_active', true)
            ->get();

        foreach ($products as $product) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$baseUrl}/product/{$product->slug}</loc>\n";
            $xml .= "    <lastmod>{$product->updated_at->format('Y-m-d')}</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.7</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Generate robots.txt for the site
     */
    public function robots(Request $request, string $subdomain)
    {
        try {
            $site = $this->siteRepository->findBySubdomain(Subdomain::fromString($subdomain));
        } catch (\Exception $e) {
            abort(404);
        }

        if (!$site) {
            abort(404);
        }

        $baseUrl = config('app.url');
        
        // If site is not published, disallow all
        if (!$site->isPublished()) {
            $content = "User-agent: *\nDisallow: /\n";
        } else {
            $content = "User-agent: *\n";
            $content .= "Allow: /\n";
            $content .= "Disallow: /dashboard/\n";
            $content .= "Disallow: /login\n";
            $content .= "Disallow: /register\n";
            $content .= "Disallow: /gb-api/\n";
            $content .= "\n";
            $content .= "Sitemap: {$baseUrl}/sitemap.xml\n";
        }

        return response($content, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
