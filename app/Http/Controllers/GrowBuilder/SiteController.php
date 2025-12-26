<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Application\GrowBuilder\DTOs\CreateSiteDTO;
use App\Application\GrowBuilder\DTOs\UpdateSiteDTO;
use App\Application\GrowBuilder\UseCases\CreateSiteUseCase;
use App\Application\GrowBuilder\UseCases\PublishSiteUseCase;
use App\Application\GrowBuilder\UseCases\UpdateSiteUseCase;
use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\TemplateRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderOrder;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SiteController extends Controller
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
        private TemplateRepositoryInterface $templateRepository,
        private CreateSiteUseCase $createSiteUseCase,
        private UpdateSiteUseCase $updateSiteUseCase,
        private PublishSiteUseCase $publishSiteUseCase,
    ) {}

    public function index(Request $request)
    {
        $sites = $this->siteRepository->findByUserId($request->user()->id);
        $siteIds = collect($sites)->map(fn($s) => $s->getId()->value())->toArray();

        // Get page views per site
        $pageViews = GrowBuilderPageView::whereIn('site_id', $siteIds)
            ->select('site_id', DB::raw('COUNT(*) as views'))
            ->groupBy('site_id')
            ->pluck('views', 'site_id')
            ->toArray();

        // Get orders per site
        $orders = GrowBuilderOrder::whereIn('site_id', $siteIds)
            ->select('site_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as revenue'))
            ->groupBy('site_id')
            ->get()
            ->keyBy('site_id');

        // Calculate totals for stats
        $stats = [
            'totalSites' => count($sites),
            'publishedSites' => collect($sites)->filter(fn($s) => $s->isPublished())->count(),
            'totalPageViews' => array_sum($pageViews),
            'totalOrders' => $orders->sum('count'),
            'totalRevenue' => $orders->sum('revenue') ?? 0,
        ];

        return Inertia::render('GrowBuilder/Dashboard', [
            'sites' => collect($sites)->map(function ($site) use ($pageViews, $orders) {
                $siteId = $site->getId()->value();
                $siteArray = $this->siteToArray($site);
                $siteArray['pageViews'] = $pageViews[$siteId] ?? 0;
                $siteArray['ordersCount'] = $orders[$siteId]->count ?? 0;
                $siteArray['revenue'] = $orders[$siteId]->revenue ?? 0;
                return $siteArray;
            }),
            'stats' => $stats,
        ]);
    }

    public function create(Request $request)
    {
        $templates = $this->templateRepository->findActive();

        return Inertia::render('GrowBuilder/Sites/Create', [
            'templates' => collect($templates)->map(fn($t) => [
                'id' => $t->getId()->value(),
                'name' => $t->getName(),
                'slug' => $t->getSlug(),
                'category' => $t->getCategory()->value(),
                'categoryLabel' => $t->getCategory()->label(),
                'description' => $t->getDescription(),
                'thumbnail' => $t->getThumbnail(),
                'previewImage' => $t->getPreviewImage(),
                'isPremium' => $t->isPremium(),
                'price' => $t->getPrice(),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|min:3|max:63|regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/',
            'template_id' => 'nullable|integer|exists:growbuilder_templates,id',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $dto = new CreateSiteDTO(
                userId: $request->user()->id,
                name: $validated['name'],
                subdomain: $validated['subdomain'],
                templateId: $validated['template_id'] ?? null,
                description: $validated['description'] ?? null,
            );

            $site = $this->createSiteUseCase->execute($dto);

            return redirect()->route('growbuilder.editor', $site->getId()->value())
                ->with('success', 'Site created successfully!');
        } catch (\DomainException $e) {
            return back()->withErrors(['subdomain' => $e->getMessage()]);
        }
    }

    public function show(Request $request, int $id)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        return Inertia::render('GrowBuilder/Sites/Show', [
            'site' => $this->siteToArray($site),
        ]);
    }

    public function settings(Request $request, int $id)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        return Inertia::render('GrowBuilder/Sites/Settings', [
            'site' => $this->siteToArray($site),
        ]);
    }

    public function analytics(Request $request, int $id)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $period = $request->get('period', '30d');
        $days = match ($period) {
            '7d' => 7,
            '90d' => 90,
            default => 30,
        };

        $startDate = now()->subDays($days);
        $previousStartDate = now()->subDays($days * 2);

        // Get daily stats
        $dailyStats = GrowBuilderPageView::where('site_id', $id)
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as views'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($row) => [
                'date' => $row->date,
                'views' => $row->views,
            ]);

        // Fill in missing dates
        $allDates = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $existing = $dailyStats->firstWhere('date', $date);
            $allDates->push([
                'date' => now()->subDays($i)->format('M j'),
                'views' => $existing ? $existing['views'] : 0,
            ]);
        }

        // Total views current period
        $totalViews = GrowBuilderPageView::where('site_id', $id)
            ->where('created_at', '>=', $startDate)
            ->count();

        // Total views previous period
        $previousViews = GrowBuilderPageView::where('site_id', $id)
            ->whereBetween('created_at', [$previousStartDate, $startDate])
            ->count();

        $viewsChange = $previousViews > 0
            ? round((($totalViews - $previousViews) / $previousViews) * 100)
            : 0;

        // Unique visitors (by IP)
        $totalVisitors = GrowBuilderPageView::where('site_id', $id)
            ->where('created_at', '>=', $startDate)
            ->distinct('ip_address')
            ->count('ip_address');

        // Device stats
        $deviceStats = GrowBuilderPageView::where('site_id', $id)
            ->where('created_at', '>=', $startDate)
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->get()
            ->map(function ($row) use ($totalViews) {
                return [
                    'device' => $row->device_type ?? 'unknown',
                    'count' => $row->count,
                    'percentage' => $totalViews > 0 ? round(($row->count / $totalViews) * 100) : 0,
                ];
            });

        // Top pages
        $topPages = GrowBuilderPageView::where('site_id', $id)
            ->where('created_at', '>=', $startDate)
            ->select('path', DB::raw('COUNT(*) as views'))
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'path' => $row->path,
                'views' => $row->views,
            ]);

        return Inertia::render('GrowBuilder/Sites/Analytics', [
            'site' => [
                'id' => $site->getId()->value(),
                'name' => $site->getName(),
                'subdomain' => $site->getSubdomain()->value(),
            ],
            'totalViews' => $totalViews,
            'totalVisitors' => $totalVisitors,
            'viewsChange' => $viewsChange,
            'dailyStats' => $allDates,
            'deviceStats' => $deviceStats,
            'topPages' => $topPages,
            'period' => $period,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'subdomain' => 'nullable|string|min:3|max:63|regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/',
            'custom_domain' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'logo' => 'nullable|string|max:500',
            'favicon' => 'nullable|string|max:500',
            // Theme
            'primary_color' => 'nullable|string|max:20',
            'secondary_color' => 'nullable|string|max:20',
            'accent_color' => 'nullable|string|max:20',
            'background_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'heading_font' => 'nullable|string|max:50',
            'body_font' => 'nullable|string|max:50',
            'border_radius' => 'nullable|integer|min:0|max:24',
            // SEO
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'og_image' => 'nullable|string|max:500',
            'google_analytics_id' => 'nullable|string|max:50',
            // Social
            'facebook' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:50',
            'linkedin' => 'nullable|string|max:255',
            // Contact
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        // Build theme array
        $theme = [
            'primaryColor' => $validated['primary_color'] ?? '#2563eb',
            'secondaryColor' => $validated['secondary_color'] ?? '#64748b',
            'accentColor' => $validated['accent_color'] ?? '#059669',
            'backgroundColor' => $validated['background_color'] ?? '#ffffff',
            'textColor' => $validated['text_color'] ?? '#1f2937',
            'headingFont' => $validated['heading_font'] ?? 'Inter',
            'bodyFont' => $validated['body_font'] ?? 'Inter',
            'borderRadius' => $validated['border_radius'] ?? 8,
        ];

        // Build SEO settings
        $seoSettings = [
            'metaTitle' => $validated['meta_title'] ?? '',
            'metaDescription' => $validated['meta_description'] ?? '',
            'ogImage' => $validated['og_image'] ?? '',
            'googleAnalyticsId' => $validated['google_analytics_id'] ?? '',
        ];

        // Build social links
        $socialLinks = [
            'facebook' => $validated['facebook'] ?? '',
            'instagram' => $validated['instagram'] ?? '',
            'twitter' => $validated['twitter'] ?? '',
            'whatsapp' => $validated['whatsapp'] ?? '',
            'linkedin' => $validated['linkedin'] ?? '',
        ];

        // Build contact info
        $contactInfo = [
            'phone' => $validated['phone'] ?? '',
            'email' => $validated['email'] ?? '',
            'address' => $validated['address'] ?? '',
        ];

        try {
            $dto = new UpdateSiteDTO(
                siteId: $id,
                userId: $request->user()->id,
                name: $validated['name'] ?? null,
                description: $validated['description'] ?? null,
                logo: $validated['logo'] ?? null,
                favicon: $validated['favicon'] ?? null,
                theme: $theme,
                contactInfo: $contactInfo,
                socialLinks: $socialLinks,
                seoSettings: $seoSettings,
                customDomain: $validated['custom_domain'] ?? null,
            );

            $this->updateSiteUseCase->execute($dto);

            return back()->with('success', 'Site updated successfully!');
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function publish(Request $request, int $id)
    {
        try {
            $this->publishSiteUseCase->execute($id, $request->user()->id);
            return back()->with('success', 'Site published successfully!');
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function unpublish(Request $request, int $id)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $site->unpublish();
        $this->siteRepository->save($site);

        return back()->with('success', 'Site unpublished');
    }

    public function destroy(Request $request, int $id)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $this->siteRepository->delete($site->getId());

        return redirect()->route('growbuilder.index')
            ->with('success', 'Site deleted successfully');
    }

    /**
     * Preview a site (for local development)
     */
    public function preview(Request $request, string $subdomain, ?string $page = null)
    {
        $site = $this->siteRepository->findBySubdomain(\App\Domain\GrowBuilder\ValueObjects\Subdomain::fromString($subdomain));

        if (!$site) {
            abort(404, 'Site not found');
        }

        $siteModel = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::find($site->getId()->value());
        
        // Get the page to render
        $pageModel = null;
        
        if ($page) {
            // Specific page requested
            $pageModel = $siteModel->pages()->where('slug', $page)->first();
        } else {
            // No page specified, get homepage
            $pageModel = $siteModel->pages()->where('is_homepage', true)->first();
        }

        if (!$pageModel) {
            // Fallback to first page
            $pageModel = $siteModel->pages()->orderBy('nav_order')->first();
        }

        if (!$pageModel) {
            abort(404, 'Page not found');
        }

        // Refresh the model to get latest data from database
        $pageModel->refresh();

        return Inertia::render('GrowBuilder/Preview/Site', [
            'site' => $this->siteToArray($site),
            'page' => [
                'id' => $pageModel->id,
                'title' => $pageModel->title,
                'slug' => $pageModel->slug,
                'content' => $pageModel->content_json,
                'isHomepage' => $pageModel->is_homepage,
            ],
            'pages' => $siteModel->pages()->where('show_in_nav', true)->orderBy('nav_order')->get()->map(fn($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'isHomepage' => $p->is_homepage,
            ]),
            'settings' => $siteModel->fresh()->settings,
        ])->withViewData(['cacheControl' => 'no-cache, no-store, must-revalidate']);
    }

    private function siteToArray($site): array
    {
        return [
            'id' => $site->getId()->value(),
            'name' => $site->getName(),
            'subdomain' => $site->getSubdomain()->value(),
            'customDomain' => $site->getCustomDomain(),
            'description' => $site->getDescription(),
            'logo' => $site->getLogo(),
            'favicon' => $site->getFavicon(),
            'theme' => $site->getTheme()?->toArray(),
            'socialLinks' => $site->getSocialLinks(),
            'contactInfo' => $site->getContactInfo(),
            'seoSettings' => $site->getSeoSettings(),
            'status' => $site->getStatus()->value(),
            'plan' => $site->getPlan()->value(),
            'planLimits' => $site->getPlan()->getLimits(),
            'url' => $site->getUrl(),
            'isPublished' => $site->isPublished(),
            'publishedAt' => $site->getPublishedAt()?->format('Y-m-d H:i:s'),
            'createdAt' => $site->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
