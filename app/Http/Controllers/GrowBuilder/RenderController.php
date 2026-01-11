<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Domain\GrowBuilder\Repositories\PageRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\Subdomain;
use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPaymentSettings;
use Illuminate\Http\Request;

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
            $page = $this->pageRepository->findBySiteIdAndSlug($site->getId(), $slug);
        } else {
            $page = $this->pageRepository->findHomepage($site->getId());
        }

        if (!$page || !$page->isPublished()) {
            abort(404);
        }

        // Track page view
        $this->trackPageView($request, $site, $page);

        // Get navigation pages
        $navPages = collect($this->pageRepository->findPublishedBySiteId($site->getId()))
            ->filter(fn($p) => $p->showInNav())
            ->sortBy(fn($p) => $p->getNavOrder())
            ->map(fn($p) => [
                'title' => $p->getTitle(),
                'slug' => $p->getSlug(),
                'isHomepage' => $p->isHomepage(),
            ])
            ->values();

        // Check if site has e-commerce enabled
        $hasEcommerce = GrowBuilderPaymentSettings::where('site_id', $site->getId()->value())->exists();

        $settings = $site->getSettings();
        
        return view('growbuilder.render', [
            'subdomain' => $site->getSubdomain()->value(),
            'site' => [
                'id' => $site->getId()->value(),
                'name' => $site->getName(),
                'subdomain' => $site->getSubdomain()->value(),
                'description' => $site->getDescription(),
                'logo' => $site->getLogo(),
                'favicon' => $site->getFavicon(),
                'favicons' => $settings['favicons'] ?? null,
                'theme' => $site->getTheme()?->toArray() ?? [],
                'socialLinks' => $site->getSocialLinks(),
                'contactInfo' => $site->getContactInfo(),
                'seoSettings' => $site->getSeoSettings(),
                'settings' => $settings,
                'hasEcommerce' => $hasEcommerce,
            ],
            'page' => [
                'title' => $page->getTitle(),
                'metaTitle' => $page->getEffectiveMetaTitle(),
                'metaDescription' => $page->getMetaDescription(),
                'ogImage' => $page->getOgImage(),
                'sections' => $page->getContent()->getSections(),
            ],
            'navigation' => $navPages,
            'settings' => [
                'splash' => $settings['splash'] ?? ['enabled' => true, 'style' => 'minimal', 'tagline' => ''],
                'navigation' => $settings['navigation'] ?? [],
                'footer' => $settings['footer'] ?? [],
            ],
        ]);
    }

    private function renderCheckout($site)
    {
        $paymentSettings = GrowBuilderPaymentSettings::where('site_id', $site->getId()->value())->first();

        return view('growbuilder.checkout', [
            'site' => [
                'name' => $site->getName(),
                'subdomain' => $site->getSubdomain()->value(),
            ],
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
