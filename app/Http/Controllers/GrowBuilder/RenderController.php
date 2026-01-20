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
        try {
            $site = $this->siteRepository->findBySubdomain(Subdomain::fromString($subdomain));
        } catch (\Exception $e) {
            abort(404);
        }

        if (!$site || !$site->isPublished()) {
            abort(404);
        }

        // Handle checkout page
        if ($slug === 'checkout') {
            return $this->renderCheckout($site);
        }

        // Get page
        if ($slug) {
            \Log::info("RenderController: Looking for page with slug: {$slug}");
            $page = $this->pageRepository->findBySiteIdAndSlug($site->getId(), $slug);
            \Log::info("RenderController: Page found: " . ($page ? 'yes' : 'no'));
            if ($page) {
                \Log::info("RenderController: Page published: " . ($page->isPublished() ? 'yes' : 'no'));
            }
        } else {
            $page = $this->pageRepository->findHomepage($site->getId());
        }

        if (!$page || !$page->isPublished()) {
            \Log::error("RenderController: Aborting 404 - Page: " . ($page ? 'found' : 'not found') . ", Published: " . ($page ? ($page->isPublished() ? 'yes' : 'no') : 'N/A'));
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
                'url' => "https://{$subdomain}.mygrownet.com",
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

        $baseUrl = "https://{$subdomain}.mygrownet.com";
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

        $baseUrl = "https://{$subdomain}.mygrownet.com";
        
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
