<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Application\GrowBuilder\DTOs\CreateSiteDTO;
use App\Application\GrowBuilder\DTOs\UpdateSiteDTO;
use App\Application\GrowBuilder\UseCases\CreateSiteUseCase;
use App\Application\GrowBuilder\UseCases\PublishSiteUseCase;
use App\Application\GrowBuilder\UseCases\UpdateSiteUseCase;
use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Domain\Module\Services\SubscriptionService;
use App\Domain\Module\Services\TierConfigurationService;
use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderOrder;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Services\GrowBuilder\AIUsageService;
use App\Services\GrowBuilder\StorageService;
use App\Services\GrowBuilder\TierRestrictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SiteController extends Controller
{
    private const MODULE_ID = 'growbuilder';

    public function __construct(
        private SiteRepositoryInterface $siteRepository,
        private CreateSiteUseCase $createSiteUseCase,
        private UpdateSiteUseCase $updateSiteUseCase,
        private PublishSiteUseCase $publishSiteUseCase,
        private \App\Application\GrowBuilder\UseCases\UnpublishSiteUseCase $unpublishSiteUseCase,
        private SubscriptionService $subscriptionService,
        private TierConfigurationService $tierConfigService,
        private StorageService $storageService,
        private AIUsageService $aiUsageService,
        private TierRestrictionService $tierRestrictionService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $sites = $this->siteRepository->findByUserId($user->id);
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

        // Get contact messages per site
        $messagesPerSite = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::whereIn('site_id', $siteIds)
            ->select('site_id', DB::raw('COUNT(*) as total'), DB::raw('SUM(CASE WHEN status = "unread" THEN 1 ELSE 0 END) as unread'))
            ->groupBy('site_id')
            ->get()
            ->keyBy('site_id');

        // Get recent unread messages across all sites (for dashboard notification)
        $recentMessages = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::whereIn('site_id', $siteIds)
            ->with('site:id,name,subdomain')
            ->where('status', 'unread')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'siteId' => $m->site_id,
                'siteName' => $m->site->name ?? 'Unknown',
                'siteSubdomain' => $m->site->subdomain ?? '',
                'name' => $m->name,
                'email' => $m->email,
                'subject' => $m->subject,
                'message' => \Illuminate\Support\Str::limit($m->message, 100),
                'createdAt' => $m->created_at->diffForHumans(),
            ]);

        $totalUnreadMessages = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::whereIn('site_id', $siteIds)
            ->where('status', 'unread')
            ->count();

        // Calculate totals for stats
        $stats = [
            'totalSites' => count($sites),
            'publishedSites' => collect($sites)->filter(fn($s) => $s->isPublished())->count(),
            'totalPageViews' => array_sum($pageViews),
            'totalOrders' => $orders->sum('count'),
            'totalRevenue' => $orders->sum('revenue') ?? 0,
            'totalMessages' => $messagesPerSite->sum('total'),
            'unreadMessages' => $totalUnreadMessages,
        ];

        // Get subscription info - use tierRestrictionService which has proper fallback defaults
        $currentTier = $this->tierRestrictionService->getUserTier($user);
        $restrictions = $this->tierRestrictionService->getRestrictions($user);
        
        $sitesLimit = $restrictions['sites_limit'];
        $sitesUsed = count($sites);
        
        // Check if user can create more sites
        $canCreateSite = $sitesUsed < $sitesLimit;

        $subscription = [
            'tier' => $currentTier,
            'tierName' => $restrictions['tier_name'],
            'sitesLimit' => $sitesLimit,
            'sitesUsed' => $sitesUsed,
            'canCreateSite' => $canCreateSite,
            'expiresAt' => null,
        ];

        // Get actual subscription expiry if exists
        $dbSubscription = DB::table('module_subscriptions')
            ->where('user_id', $user->id)
            ->where('module_id', self::MODULE_ID)
            ->where('status', 'active')
            ->first();
        
        if ($dbSubscription) {
            $subscription['expiresAt'] = $dbSubscription->expires_at;
        }

        // Get AI usage stats
        $aiUsage = $this->aiUsageService->getUsageStats($user);

        // Get comprehensive tier restrictions
        $tierRestrictions = $this->tierRestrictionService->getRestrictions($user);

        // Get user's total storage stats across all sites
        $userStorageStats = $this->storageService->getUserStorageStats($user->id, $currentTier);

        // For admins: get available tiers for testing
        $availableTiers = null;
        $isAdmin = $user->is_admin;
        if ($isAdmin) {
            $allTiers = $this->tierConfigService->getTiers(self::MODULE_ID);
            
            // If no tiers in DB, use fallback
            if (empty($allTiers)) {
                $allTiers = [
                    'free' => ['name' => 'Free'],
                    'starter' => ['name' => 'Starter'],
                    'business' => ['name' => 'Business'],
                    'agency' => ['name' => 'Agency'],
                ];
            }
            
            $availableTiers = collect($allTiers)->map(fn($t, $key) => [
                'key' => $key,
                'name' => $t['name'] ?? ucfirst($key),
            ])->values()->toArray();
        }

        return Inertia::render('GrowBuilder/Dashboard', [
            'sites' => collect($sites)->map(function ($site) use ($pageViews, $orders, $messagesPerSite) {
                $siteId = $site->getId()->value();
                $siteArray = $this->siteToArray($site);
                $siteArray['pageViews'] = $pageViews[$siteId] ?? 0;
                $siteArray['ordersCount'] = $orders[$siteId]->count ?? 0;
                $siteArray['revenue'] = $orders[$siteId]->revenue ?? 0;
                $siteArray['messagesCount'] = $messagesPerSite[$siteId]->total ?? 0;
                $siteArray['unreadMessages'] = $messagesPerSite[$siteId]->unread ?? 0;
                
                // Add storage info from the model
                $siteModel = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::find($siteId);
                if ($siteModel) {
                    $siteArray['storageUsed'] = $siteModel->storage_used ?? 0;
                    $siteArray['storageLimit'] = $siteModel->storage_limit ?? 104857600;
                    $siteArray['storageUsedFormatted'] = $siteModel->storage_used_formatted;
                    $siteArray['storageLimitFormatted'] = $siteModel->storage_limit_formatted;
                    $siteArray['storagePercentage'] = $siteModel->storage_percentage;
                    
                    // Add site health suggestions
                    $siteArray['healthSuggestions'] = $this->getSiteHealthSuggestions($siteModel, $site);
                }
                
                return $siteArray;
            }),
            'stats' => $stats,
            'subscription' => $subscription,
            'aiUsage' => $aiUsage,
            'tierRestrictions' => $tierRestrictions,
            'storageStats' => $userStorageStats, // User's total storage across all sites
            'availableTiers' => $availableTiers,
            'isAdmin' => $isAdmin,
            // Site templates and industries for the create wizard modal
            'siteTemplates' => \App\Models\GrowBuilder\SiteTemplate::with('pages')
                ->active()
                ->orderBy('sort_order')
                ->get()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'slug' => $t->slug,
                    'description' => $t->description,
                    'industry' => $t->industry,
                    'thumbnail' => $t->thumbnail_url,
                    'theme' => $t->theme,
                    'isPremium' => $t->is_premium,
                    'pagesCount' => $t->pages->count(),
                    'pages' => $t->pages->map(fn($p) => [
                        'title' => $p->title,
                        'slug' => $p->slug,
                        'isHomepage' => $p->is_homepage,
                    ]),
                ]),
            'industries' => \App\Models\GrowBuilder\SiteTemplateIndustry::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn($i) => [
                    'slug' => $i->slug,
                    'name' => $i->name,
                    'icon' => $i->icon,
                ]),
            // Contact messages for dashboard
            'recentMessages' => $recentMessages,
        ]);
    }

    public function create(Request $request)
    {
        // Get site templates (full website templates)
        $siteTemplates = \App\Models\GrowBuilder\SiteTemplate::with('pages')
            ->active()
            ->orderBy('sort_order')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'description' => $t->description,
                'industry' => $t->industry,
                'thumbnail' => $t->thumbnail_url,
                'theme' => $t->theme,
                'isPremium' => $t->is_premium,
                'pagesCount' => $t->pages->count(),
                'pages' => $t->pages->map(fn($p) => [
                    'title' => $p->title,
                    'slug' => $p->slug,
                    'isHomepage' => $p->is_homepage,
                ]),
            ]);

        // Get industries for filtering
        $industries = \App\Models\GrowBuilder\SiteTemplateIndustry::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($i) => [
                'slug' => $i->slug,
                'name' => $i->name,
                'icon' => $i->icon,
            ]);

        return Inertia::render('GrowBuilder/Sites/Create', [
            'siteTemplates' => $siteTemplates,
            'industries' => $industries,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        
        // Check subscription limits before creating - use tierRestrictionService for proper fallbacks
        $restrictions = $this->tierRestrictionService->getRestrictions($user);
        $currentTier = $restrictions['tier'];
        $sitesLimit = $restrictions['sites_limit'];
        
        $currentSites = $this->siteRepository->findByUserId($user->id);
        $sitesUsed = count($currentSites);
        
        // Enforce site limit for all tiers
        if ($sitesUsed >= $sitesLimit) {
            // Check if user is at highest tier (agency)
            $isHighestTier = $currentTier === 'agency';
            $message = $isHighestTier 
                ? "You've reached your maximum site limit ({$sitesLimit}). Contact support if you need more."
                : "You've reached your site limit ({$sitesLimit}). Please upgrade your subscription to create more sites.";
            
            return back()->withErrors([
                'limit' => $message
            ]);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|min:3|max:63|regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/',
            'site_template_id' => 'nullable|integer|exists:site_templates,id',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $dto = new CreateSiteDTO(
                userId: $request->user()->id,
                name: $validated['name'],
                subdomain: $validated['subdomain'],
                templateId: $validated['site_template_id'] ?? null,
                description: $validated['description'] ?? null,
            );

            $site = $this->createSiteUseCase->execute($dto);

            // Apply site template if selected (full website template)
            if (!empty($validated['site_template_id'])) {
                $applySiteTemplate = new \App\Application\GrowBuilder\UseCases\ApplySiteTemplateUseCase(
                    $this->siteRepository
                );
                $applySiteTemplate->execute($site->getId()->value(), $validated['site_template_id']);
            }

            // Set storage limit based on the site's plan (each site gets its full tier allocation)
            // The site's plan is set during creation based on user's current subscription
            $siteModel = GrowBuilderSite::find($site->getId()->value());
            $sitePlan = $siteModel->plan ?? $currentTier;
            $storageLimit = $this->storageService->getStorageLimitForTier($sitePlan);
            $siteModel->update([
                'plan' => $sitePlan,
                'storage_limit' => $storageLimit,
            ]);

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

    /**
     * List contact messages for a site (owner view)
     */
    public function messages(Request $request, int $id)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $query = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::forSite($id)
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $messages = $query->paginate(20);
        $unreadCount = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::forSite($id)->unread()->count();
        $totalCount = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::forSite($id)->count();

        return Inertia::render('GrowBuilder/Sites/Messages', [
            'site' => $this->siteToArray($site),
            'messages' => $messages,
            'unreadCount' => $unreadCount,
            'totalCount' => $totalCount,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * Show a single message
     */
    public function showMessage(Request $request, int $id, int $messageId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $message = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::forSite($id)
            ->findOrFail($messageId);

        // Mark as read
        $message->markAsRead();

        return Inertia::render('GrowBuilder/Sites/MessageDetail', [
            'site' => $this->siteToArray($site),
            'message' => $message,
        ]);
    }

    /**
     * Reply to a message
     */
    public function replyMessage(Request $request, int $id, int $messageId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $message = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::forSite($id)
            ->findOrFail($messageId);

        $validated = $request->validate([
            'reply' => 'required|string|max:5000',
        ]);

        $message->update([
            'reply' => $validated['reply'],
            'status' => 'replied',
            'replied_at' => now(),
        ]);

        // TODO: Send email notification to the sender

        return back()->with('success', 'Reply sent successfully.');
    }

    /**
     * Update message status
     */
    public function updateMessageStatus(Request $request, int $id, int $messageId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $message = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::forSite($id)
            ->findOrFail($messageId);

        $validated = $request->validate([
            'status' => 'required|in:unread,read,replied,archived',
        ]);

        $message->update(['status' => $validated['status']]);

        return back()->with('success', 'Status updated.');
    }

    /**
     * Delete a message
     */
    public function deleteMessage(Request $request, int $id, int $messageId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $message = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::forSite($id)
            ->findOrFail($messageId);

        $message->delete();

        return redirect()->route('growbuilder.sites.messages', $id)
            ->with('success', 'Message deleted.');
    }

    /**
     * Export messages to CSV
     */
    public function exportMessages(Request $request, int $id)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $messages = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::forSite($id)
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = $site->getName() . '_messages_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($messages) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Date', 'Name', 'Email', 'Phone', 'Subject', 'Message', 'Status', 'Reply']);
            
            foreach ($messages as $message) {
                fputcsv($file, [
                    $message->created_at->format('Y-m-d H:i'),
                    $message->name,
                    $message->email,
                    $message->phone ?? '',
                    $message->subject ?? '',
                    $message->message,
                    $message->status,
                    $message->reply ?? '',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
            // Splash Screen
            'splash_enabled' => 'nullable|boolean',
            'splash_style' => 'nullable|string|in:none,minimal,pulse,wave,gradient,particles,elegant',
            'splash_tagline' => 'nullable|string|max:100',
            // SEO
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'og_image' => 'nullable|string|max:2000',
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
                subdomain: $validated['subdomain'] ?? null,
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
            
            // Update settings JSON (logo, splash, etc.)
            $siteModel = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::find($id);
            if ($siteModel) {
                $settings = $siteModel->settings ?? [];
                
                // Sync logo to settings.navigation.logo for editor compatibility
                if (isset($validated['logo'])) {
                    $settings['navigation'] = $settings['navigation'] ?? [];
                    $settings['navigation']['logo'] = $validated['logo'];
                }
                
                // Save splash screen settings
                if ($request->has('splash_enabled') || $request->has('splash_style') || $request->has('splash_tagline')) {
                    $settings['splash'] = [
                        'enabled' => $validated['splash_enabled'] ?? true,
                        'style' => $validated['splash_style'] ?? 'minimal',
                        'tagline' => $validated['splash_tagline'] ?? '',
                    ];
                }
                
                $siteModel->settings = $settings;
                $siteModel->save();
            }

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
        try {
            $this->unpublishSiteUseCase->execute($id, $request->user()->id);
            return back()->with('success', 'Site and all pages unpublished successfully!');
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Manage site users (for site owner)
     */
    public function users(Request $request, int $id)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $siteModel = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::find($id);
        
        $users = \App\Infrastructure\GrowBuilder\Models\SiteUser::where('site_id', $id)
            ->with('role')
            ->latest()
            ->paginate(20);

        $roles = \App\Infrastructure\GrowBuilder\Models\SiteRole::where('site_id', $id)
            ->orderBy('level', 'desc')
            ->get();

        return Inertia::render('GrowBuilder/Sites/Users', [
            'site' => $this->siteToArray($site),
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Create a new site user (admin)
     */
    public function createUser(Request $request, int $id)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
            'site_role_id' => 'required|exists:site_roles,id',
        ]);

        // Check email uniqueness for this site
        if (\App\Infrastructure\GrowBuilder\Models\SiteUser::where('site_id', $id)
            ->where('email', $validated['email'])
            ->exists()
        ) {
            return back()->withErrors(['email' => 'This email is already registered on this site.']);
        }

        // Verify role belongs to this site
        $role = \App\Infrastructure\GrowBuilder\Models\SiteRole::where('site_id', $id)
            ->where('id', $validated['site_role_id'])
            ->first();

        if (!$role) {
            return back()->withErrors(['site_role_id' => 'Invalid role selected.']);
        }

        \App\Infrastructure\GrowBuilder\Models\SiteUser::create([
            'site_id' => $id,
            'site_role_id' => $role->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'status' => 'active',
        ]);

        return back()->with('success', 'User created successfully!');
    }

    /**
     * Update site user role
     */
    public function updateUserRole(Request $request, int $id, int $userId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $validated = $request->validate([
            'site_role_id' => 'required|exists:site_roles,id',
        ]);

        $user = \App\Infrastructure\GrowBuilder\Models\SiteUser::where('site_id', $id)
            ->where('id', $userId)
            ->firstOrFail();

        // Verify role belongs to this site
        $role = \App\Infrastructure\GrowBuilder\Models\SiteRole::where('site_id', $id)
            ->where('id', $validated['site_role_id'])
            ->first();

        if (!$role) {
            return back()->withErrors(['site_role_id' => 'Invalid role selected.']);
        }

        $user->update(['site_role_id' => $role->id]);

        return back()->with('success', 'User role updated successfully!');
    }

    /**
     * Delete site user
     */
    public function deleteUser(Request $request, int $id, int $userId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $user = \App\Infrastructure\GrowBuilder\Models\SiteUser::where('site_id', $id)
            ->where('id', $userId)
            ->firstOrFail();

        $user->delete();

        return back()->with('success', 'User deleted successfully!');
    }

    /**
     * Manage site roles
     */
    public function roles(Request $request, int $id)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $roles = \App\Infrastructure\GrowBuilder\Models\SiteRole::where('site_id', $id)
            ->with('permissions')
            ->orderBy('level', 'desc')
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'description' => $role->description,
                    'level' => $role->level,
                    'type' => $role->type ?? 'client',
                    'icon' => $role->icon,
                    'color' => $role->color,
                    'is_system' => $role->is_system,
                    'users_count' => $role->users()->count(),
                    'permissions' => $role->permissions->pluck('slug')->toArray(),
                ];
            });

        // Get all available permissions grouped
        $permissions = \App\Infrastructure\GrowBuilder\Models\SitePermission::all()
            ->groupBy('group_name')
            ->map(function ($group) {
                return $group->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'description' => $p->description,
                ])->values()->toArray();
            })->toArray();

        return Inertia::render('GrowBuilder/Sites/Roles', [
            'site' => $this->siteToArray($site),
            'roles' => $roles->toArray(),
            'permissions' => $permissions,
        ]);
    }

    /**
     * Get all permissions (API endpoint)
     */
    public function permissions(Request $request, int $id)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $permissions = \App\Infrastructure\GrowBuilder\Models\SitePermission::all()
            ->groupBy('group_name')
            ->map(function ($group) {
                return $group->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'description' => $p->description,
                ]);
            });

        return response()->json($permissions);
    }

    /**
     * Create a custom role
     */
    public function createRole(Request $request, int $id)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'required|string|max:50|regex:/^[a-z0-9-]+$/',
            'description' => 'nullable|string|max:255',
            'level' => 'required|integer|min:1|max:99',
            'type' => 'required|in:staff,client',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:site_permissions,slug',
        ]);

        // Check slug uniqueness for this site
        if (\App\Infrastructure\GrowBuilder\Models\SiteRole::where('site_id', $id)
            ->where('slug', $validated['slug'])
            ->exists()
        ) {
            return back()->withErrors(['slug' => 'This role slug already exists.']);
        }

        $role = \App\Infrastructure\GrowBuilder\Models\SiteRole::create([
            'site_id' => $id,
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'level' => $validated['level'],
            'type' => $validated['type'],
            'icon' => $validated['icon'] ?? null,
            'color' => $validated['color'] ?? null,
            'is_system' => false,
        ]);

        // Assign permissions
        if (!empty($validated['permissions'])) {
            $permissionIds = \App\Infrastructure\GrowBuilder\Models\SitePermission::whereIn('slug', $validated['permissions'])
                ->pluck('id')
                ->toArray();
            $role->permissions()->sync($permissionIds);
        }

        return back()->with('success', 'Role created successfully!');
    }

    /**
     * Update a role
     */
    public function updateRole(Request $request, int $id, int $roleId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $role = \App\Infrastructure\GrowBuilder\Models\SiteRole::where('site_id', $id)
            ->where('id', $roleId)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'level' => 'required|integer|min:1|max:99',
            'type' => 'required|in:staff,client',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:site_permissions,slug',
        ]);

        // System roles can be edited but with restrictions
        if ($role->is_system) {
            // Can't change level of admin role
            if ($role->slug === 'admin' && $validated['level'] !== 100) {
                return back()->withErrors(['level' => 'Cannot change admin role level.']);
            }
        }

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'level' => $validated['level'],
            'type' => $validated['type'],
            'icon' => $validated['icon'] ?? null,
            'color' => $validated['color'] ?? null,
        ]);

        // Update permissions (admin role always has all permissions via level check)
        if ($role->slug !== 'admin') {
            $permissionIds = \App\Infrastructure\GrowBuilder\Models\SitePermission::whereIn('slug', $validated['permissions'] ?? [])
                ->pluck('id')
                ->toArray();
            $role->permissions()->sync($permissionIds);
        }

        return back()->with('success', 'Role updated successfully!');
    }

    /**
     * Delete a custom role
     */
    public function deleteRole(Request $request, int $id, int $roleId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $role = \App\Infrastructure\GrowBuilder\Models\SiteRole::where('site_id', $id)
            ->where('id', $roleId)
            ->firstOrFail();

        // Cannot delete system roles
        if ($role->is_system) {
            return back()->withErrors(['error' => 'Cannot delete system roles.']);
        }

        // Check if any users have this role
        $usersCount = $role->users()->count();
        if ($usersCount > 0) {
            return back()->withErrors(['error' => "Cannot delete role. {$usersCount} user(s) are assigned to this role."]);
        }

        $role->delete();

        return back()->with('success', 'Role deleted successfully!');
    }

    public function destroy(Request $request, int $id)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($id));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        // Schedule for deletion (30 days grace period) instead of immediate delete
        $siteModel = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::find($id);
        $siteModel->update([
            'status' => 'deleted',
            'scheduled_deletion_at' => now()->addDays(30),
            'deletion_reason' => 'User requested deletion',
        ]);

        return redirect()->route('growbuilder.index')
            ->with('success', 'Site scheduled for deletion. It will be permanently removed in 30 days.');
    }

    /**
     * Restore a deleted site (within grace period)
     */
    public function restore(Request $request, int $id)
    {
        $siteModel = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::find($id);

        if (!$siteModel || $siteModel->user_id !== $request->user()->id) {
            abort(404);
        }

        if ($siteModel->status !== 'deleted') {
            return back()->with('error', 'This site is not scheduled for deletion.');
        }

        // Restore to draft status
        $siteModel->update([
            'status' => 'draft',
            'scheduled_deletion_at' => null,
            'deletion_reason' => null,
        ]);

        return redirect()->route('growbuilder.sites.settings', $id)
            ->with('success', 'Site restored successfully. It is now in draft mode.');
    }

    /**
     * Preview a site (for local development or published sites)
     */
    public function preview(Request $request, string $subdomain, ?string $page = null)
    {
        $site = $this->siteRepository->findBySubdomain(\App\Domain\GrowBuilder\ValueObjects\Subdomain::fromString($subdomain));

        if (!$site) {
            abort(404, 'Site not found');
        }

        // Check if site is accessible (unless owner is viewing)
        $isOwner = $request->user() && $request->user()->id === $site->getUserId();
        $status = $site->getStatus();
        
        if (!$status->isPublished() && !$isOwner) {
            // Determine the appropriate message based on status
            $statusType = $status->value();
            $message = match ($statusType) {
                'draft' => 'This site is currently under construction. Please check back soon!',
                'maintenance' => 'This site is temporarily offline for maintenance. We\'ll be back shortly!',
                'suspended' => 'This site is currently unavailable.',
                default => 'This site is not available at the moment.',
            };
            
            return Inertia::render('GrowBuilder/Preview/Offline', [
                'siteName' => $site->getName(),
                'status' => $statusType,
                'message' => $message,
                'wasPublished' => $site->getPublishedAt() !== null,
            ]);
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

        // Get products for the site (for products section)
        $products = \App\Infrastructure\GrowBuilder\Models\GrowBuilderProduct::where('site_id', $siteModel->id)
            ->where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->limit(12)
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

        // Get site owner's tier for watermark display
        $siteOwner = \App\Models\User::find($site->getUserId());
        $ownerTier = $siteOwner ? $this->tierRestrictionService->getUserTier($siteOwner) : 'free';
        $showWatermark = $ownerTier === 'free';

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
            'products' => $products,
            'isPreview' => !$site->isPublished() && $isOwner, // Flag for owner preview mode
            'showWatermark' => $showWatermark, // Show "Powered by MyGrowNet" for free tier
        ])->withViewData(['cacheControl' => 'no-cache, no-store, must-revalidate']);
    }

    /**
     * Switch user's tier (admin only - for testing)
     */
    public function switchTier(Request $request)
    {
        $user = $request->user();
        
        // Only admins can switch tiers
        if (!$user->is_admin) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'tier' => 'required|string',
        ]);

        $tierKey = $validated['tier'];
        
        // Verify tier exists (check DB first, then fallback)
        $allTiers = $this->tierConfigService->getTiers(self::MODULE_ID);
        if (empty($allTiers)) {
            $allTiers = [
                'free' => ['name' => 'Free'],
                'starter' => ['name' => 'Starter'],
                'business' => ['name' => 'Business'],
                'agency' => ['name' => 'Agency'],
            ];
        }
        
        if (!isset($allTiers[$tierKey])) {
            return back()->withErrors(['tier' => 'Invalid tier selected.']);
        }

        // Update or create subscription
        $subscription = DB::table('module_subscriptions')
            ->where('user_id', $user->id)
            ->where('module_id', self::MODULE_ID)
            ->first();

        if ($subscription) {
            DB::table('module_subscriptions')
                ->where('id', $subscription->id)
                ->update([
                    'subscription_tier' => $tierKey,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('module_subscriptions')->insert([
                'user_id' => $user->id,
                'module_id' => self::MODULE_ID,
                'subscription_tier' => $tierKey,
                'status' => 'active',
                'started_at' => now(),
                'expires_at' => now()->addYear(),
                'billing_cycle' => 'monthly',
                'amount' => 0,
                'currency' => 'ZMW',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Clear cached tier data - clear all possible tier caches
        $allTierKeys = ['free', 'starter', 'business', 'agency'];
        foreach ($allTierKeys as $t) {
            \Illuminate\Support\Facades\Cache::forget("growbuilder_restrictions:{$user->id}:{$t}");
        }
        \Illuminate\Support\Facades\Cache::forget("user_tier_{$user->id}_" . self::MODULE_ID);
        \Illuminate\Support\Facades\Cache::forget("module_sub_{$user->id}_" . self::MODULE_ID);
        $this->tierRestrictionService->clearCache($user);
        $this->aiUsageService->clearCache($user);

        return back()->with('success', "Switched to {$allTiers[$tierKey]['name']} tier.");
    }

    /**
     * Show product detail page
     */
    public function showProduct(Request $request, string $subdomain, string $slug)
    {
        $site = $this->siteRepository->findBySubdomain(\App\Domain\GrowBuilder\ValueObjects\Subdomain::fromString($subdomain));

        if (!$site) {
            abort(404, 'Site not found');
        }

        // Check if site is accessible
        $isOwner = $request->user() && $request->user()->id === $site->getUserId();
        if (!$site->isPublished() && !$isOwner) {
            abort(404, 'Site not found');
        }

        $siteModel = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::find($site->getId()->value());

        // Get the product
        $product = \App\Infrastructure\GrowBuilder\Models\GrowBuilderProduct::where('site_id', $siteModel->id)
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            abort(404, 'Product not found');
        }

        // Get related products (same category or featured)
        $relatedProducts = \App\Infrastructure\GrowBuilder\Models\GrowBuilderProduct::where('site_id', $siteModel->id)
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->when($product->category, function ($query) use ($product) {
                $query->where('category', $product->category);
            })
            ->orderBy('is_featured', 'desc')
            ->limit(4)
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
                'inStock' => $p->isInStock(),
                'isFeatured' => $p->is_featured,
                'hasDiscount' => $p->hasDiscount(),
                'discountPercentage' => $p->discount_percentage,
            ]);

        return Inertia::render('GrowBuilder/Preview/Product', [
            'site' => $this->siteToArray($site),
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'priceFormatted' => $product->formatted_price,
                'comparePrice' => $product->compare_price,
                'comparePriceFormatted' => $product->compare_price ? 'K' . number_format($product->compare_price / 100, 2) : null,
                'image' => $product->main_image,
                'images' => $product->images ?? [],
                'description' => $product->description,
                'shortDescription' => $product->short_description,
                'category' => $product->category,
                'sku' => $product->sku,
                'inStock' => $product->isInStock(),
                'stockQuantity' => $product->stock_quantity,
                'trackStock' => $product->track_stock,
                'isFeatured' => $product->is_featured,
                'hasDiscount' => $product->hasDiscount(),
                'discountPercentage' => $product->discount_percentage,
            ],
            'relatedProducts' => $relatedProducts,
            'settings' => $siteModel->settings,
        ]);
    }

    /**
     * Show checkout page
     */
    public function checkout(Request $request, string $subdomain)
    {
        $site = $this->siteRepository->findBySubdomain(\App\Domain\GrowBuilder\ValueObjects\Subdomain::fromString($subdomain));

        if (!$site) {
            abort(404, 'Site not found');
        }

        // Check if site is accessible
        $isOwner = $request->user() && $request->user()->id === $site->getUserId();
        if (!$site->isPublished() && !$isOwner) {
            abort(404, 'Site not found');
        }

        $siteModel = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::find($site->getId()->value());

        // Get payment settings
        $paymentSettings = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPaymentSettings::where('site_id', $siteModel->id)->first();
        
        // Check for new payment gateway config
        $paymentConfig = \App\Models\GrowBuilder\SitePaymentConfig::where('site_id', $siteModel->id)
            ->where('is_active', true)
            ->first();

        $paymentMethods = [];
        
        // Add legacy payment methods
        if ($paymentSettings) {
            if ($paymentSettings->momo_enabled) {
                $paymentMethods[] = ['id' => 'momo', 'name' => 'MTN Mobile Money', 'icon' => '📱'];
            }
            if ($paymentSettings->airtel_enabled) {
                $paymentMethods[] = ['id' => 'airtel', 'name' => 'Airtel Money', 'icon' => '📱'];
            }
            if ($paymentSettings->whatsapp_enabled) {
                $paymentMethods[] = ['id' => 'whatsapp', 'name' => 'WhatsApp Order', 'icon' => '💬'];
            }
            if ($paymentSettings->cod_enabled) {
                $paymentMethods[] = ['id' => 'cod', 'name' => 'Cash on Delivery', 'icon' => '💵'];
            }
            if ($paymentSettings->bank_enabled) {
                $paymentMethods[] = ['id' => 'bank', 'name' => 'Bank Transfer', 'icon' => '🏦'];
            }
        }

        // Add new payment gateway if configured
        if ($paymentConfig) {
            $paymentMethods[] = ['id' => 'online', 'name' => 'Pay Online', 'icon' => '💳'];
        }

        // Default to COD if no payment methods configured
        if (empty($paymentMethods)) {
            $paymentMethods[] = ['id' => 'cod', 'name' => 'Cash on Delivery', 'icon' => '💵'];
        }

        return Inertia::render('GrowBuilder/Preview/Checkout', [
            'site' => $this->siteToArray($site),
            'settings' => $siteModel->settings,
            'paymentMethods' => $paymentMethods,
        ]);
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
            'settings' => $site->getSettings(),
            'status' => $site->getStatus()->value(),
            'plan' => $site->getPlan()->value(),
            'planLimits' => $site->getPlan()->getLimits(),
            'url' => $site->getUrl(),
            'isPublished' => $site->isPublished(),
            'publishedAt' => $site->getPublishedAt()?->format('Y-m-d H:i:s'),
            'createdAt' => $site->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get site health suggestions for dashboard
     */
    private function getSiteHealthSuggestions($siteModel, $site): array
    {
        $suggestions = [];
        
        // Check if site is published
        if (!$site->isPublished()) {
            $suggestions[] = [
                'type' => 'warning',
                'icon' => 'globe',
                'message' => 'Your site is not published yet',
                'action' => 'Publish',
                'actionRoute' => 'growbuilder.sites.settings',
            ];
        }
        
        // Check for logo
        if (empty($site->getLogo())) {
            $suggestions[] = [
                'type' => 'info',
                'icon' => 'photo',
                'message' => 'Add a logo to build your brand',
                'action' => 'Add Logo',
                'actionRoute' => 'growbuilder.sites.settings',
            ];
        }
        
        // Check for SEO settings
        $seoSettings = $site->getSeoSettings();
        if (empty($seoSettings['metaTitle']) || empty($seoSettings['metaDescription'])) {
            $suggestions[] = [
                'type' => 'info',
                'icon' => 'magnifying-glass',
                'message' => 'Improve SEO with meta title & description',
                'action' => 'Add SEO',
                'actionRoute' => 'growbuilder.sites.settings',
            ];
        }
        
        // Check for contact info
        $contactInfo = $site->getContactInfo();
        if (empty($contactInfo['phone']) && empty($contactInfo['email'])) {
            $suggestions[] = [
                'type' => 'info',
                'icon' => 'phone',
                'message' => 'Add contact info so customers can reach you',
                'action' => 'Add Contact',
                'actionRoute' => 'growbuilder.sites.settings',
            ];
        }
        
        // Check for social links
        $socialLinks = $site->getSocialLinks();
        $hasSocial = !empty($socialLinks['facebook']) || !empty($socialLinks['instagram']) || !empty($socialLinks['whatsapp']);
        if (!$hasSocial) {
            $suggestions[] = [
                'type' => 'info',
                'icon' => 'share',
                'message' => 'Connect your social media accounts',
                'action' => 'Add Social',
                'actionRoute' => 'growbuilder.sites.settings',
            ];
        }
        
        // Check page count
        $pageCount = $siteModel->pages()->count();
        if ($pageCount < 2) {
            $suggestions[] = [
                'type' => 'info',
                'icon' => 'document',
                'message' => 'Add more pages to your site',
                'action' => 'Add Pages',
                'actionRoute' => 'growbuilder.editor',
            ];
        }
        
        // Limit to top 3 suggestions
        return array_slice($suggestions, 0, 3);
    }
}
