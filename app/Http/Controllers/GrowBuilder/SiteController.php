<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\TemplateRepositoryInterface;
use App\Domain\GrowBuilder\Services\SiteAnalyticsService;
use App\Domain\GrowBuilder\Services\SiteDashboardService;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Http\Controllers\Controller;
use App\Services\GrowBuilder\TierRestrictionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SiteController extends Controller
{
    private const MODULE_ID = 'growbuilder';

    public function __construct(
        private SiteRepositoryInterface $siteRepository,
        private TemplateRepositoryInterface $templateRepository,
        private SiteDashboardService $dashboardService,
        private SiteAnalyticsService $analyticsService,
        private TierRestrictionService $tierRestrictionService,
    ) {}

    public function test(Request $request)
    {
        return Inertia::render('GrowBuilder/Dashboard', [
            'sites' => [],
            'stats' => ['totalSites' => 0],
            'subscription' => ['tier' => 'free'],
            'modules' => [],
        ]);
    }
    
    public function index(Request $request)
    {
        $user = $request->user();
        
        $sites = $this->dashboardService->getUserSitesData($user->id);

        $stats = $this->dashboardService->getDashboardStats($sites);
        $pageViewsPerSite = $this->dashboardService->getPageViewsPerSite($sites);
        $messageCounts = $this->dashboardService->getMessageCounts($sites);
        $rawDailyViews = $this->dashboardService->getDailyPageViews($sites);

        $sitesData = $sites->map(fn($site) => $this->dashboardService->formatSiteData($site, $pageViewsPerSite, $messageCounts, $rawDailyViews));

        // Get user's actual subscription data
        $subscriptionService = app(\App\Domain\Module\Services\SubscriptionService::class);
        $tierConfigService = app(\App\Domain\Module\Services\TierConfigurationService::class);
        
        $userTier = $subscriptionService->getUserTier($user, 'growbuilder') ?? 'free';
        $tierConfig = $tierConfigService->getTierConfig('growbuilder', $userTier);
        
        $subscription = [
            'tier' => $userTier,
            'tierName' => $tierConfig['name'] ?? ucfirst($userTier),
            'sitesLimit' => $tierConfigService->getLimit('growbuilder', $userTier, 'sites'),
            'sitesUsed' => $sites->count(),
            'canCreateSite' => $sites->count() < $tierConfigService->getLimit('growbuilder', $userTier, 'sites'),
            'storageLimit' => $tierConfigService->getLimit('growbuilder', $userTier, 'storage_mb'),
            'pagesLimit' => $tierConfigService->getLimit('growbuilder', $userTier, 'pages'),
            'productsLimit' => $tierConfigService->getLimit('growbuilder', $userTier, 'products'),
            'price' => $tierConfig['price_monthly'] ?? 0,
            'expiresAt' => null, // TODO: Get actual expiration
        ];

        // Get available tiers for upgrade options
        $availableTiers = collect(['free', 'starter', 'business', 'agency'])->map(function($tierKey) use ($tierConfigService) {
            $config = $tierConfigService->getTierConfig('growbuilder', $tierKey);
            return [
                'key' => $tierKey,
                'name' => $config['name'] ?? ucfirst($tierKey),
                'price' => $config['price_monthly'] ?? 0,
                'storageLimit' => $tierConfigService->getLimit('growbuilder', $tierKey, 'storage_mb'),
                'sitesLimit' => $tierConfigService->getLimit('growbuilder', $tierKey, 'sites'),
                'pagesLimit' => $tierConfigService->getLimit('growbuilder', $tierKey, 'pages'),
                'productsLimit' => $tierConfigService->getLimit('growbuilder', $tierKey, 'products'),
            ];
        })->toArray();

        // Get site templates for the create wizard
        $siteTemplates = \App\Models\GrowBuilder\SiteTemplate::where('is_active', true)
            ->with('pages')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'slug' => $template->slug,
                    'description' => $template->description,
                    'industry' => $template->industry,
                    'thumbnail' => $template->thumbnail_url,
                    'theme' => $template->theme,
                    'isPremium' => $template->is_premium,
                    'pagesCount' => $template->pages->count(),
                    'pages' => $template->pages->map(function ($page) {
                        return [
                            'title' => $page->title,
                            'slug' => $page->slug,
                            'isHomepage' => $page->is_homepage,
                        ];
                    })->toArray(),
                ];
            })->values()->toArray();

        // Get industries for filtering
        $industries = array_map(fn($industry) => [
            'slug' => \Str::slug($industry),
            'name' => $industry,
            'icon' => 'BuildingOfficeIcon',
        ], $this->templateRepository->getIndustries());

        // Recent unread messages across all user's sites
        $recentMessages = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::whereIn('site_id', $sites->pluck('id'))
            ->where('status', 'unread')
            ->with('site')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'siteId' => $msg->site_id,
                    'siteName' => $msg->site->name ?? 'Unknown',
                    'siteSubdomain' => $msg->site->subdomain ?? '',
                    'name' => $msg->name,
                    'email' => $msg->email,
                    'subject' => $msg->subject,
                    'message' => $msg->message,
                    'status' => $msg->status,
                    'createdAt' => $msg->created_at->diffForHumans(),
                ];
            })->toArray();

        // Recent activity feed from agency activity logs
        $recentActivity = [];
        $agency = $user->ownedAgencies()->first() ?? $user->agencies()->first();
        if ($agency) {
            $recentActivity = \App\Models\AgencyActivityLog::where('agency_id', $agency->id)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'actionType' => $log->action_type,
                        'entityType' => $log->entity_type,
                        'description' => $log->description,
                        'userName' => $log->user?->name ?? 'System',
                        'createdAt' => $log->created_at->diffForHumans(),
                    ];
                })->toArray();
        }

        return Inertia::render('GrowBuilder/Dashboard', [
            'sites' => $sitesData,
            'stats' => $stats,
            'subscription' => $subscription,
            'availableTiers' => $availableTiers,
            'isAdmin' => $user->hasRole(['Administrator', 'admin', 'superadmin']),
            'clients' => $this->getClientsForUser($user),
            'siteTemplates' => $siteTemplates,
            'industries' => $industries,
            'recentMessages' => $recentMessages,
            'recentActivity' => $recentActivity,
            'modules' => [],
        ]);
    }

    /**
     * Get clients for the current user (agency users only)
     */
    private function getClientsForUser($user): array
    {
        // Check if user has agency access
        $agency = $user->ownedAgencies()->first() ?? $user->agencies()->first();
        
        if (!$agency) {
            return [];
        }

        return \App\Models\AgencyClient::where('agency_id', $agency->id)
            ->where('status', '!=', 'archived')
            ->orderBy('client_name')
            ->get(['id', 'client_name', 'company_name', 'client_type', 'status'])
            ->toArray();
    }

    // Placeholder methods to prevent route errors
    public function create(Request $request)
    {
        $user = $request->user();
        
        // Get user's agency (if they have one)
        $agency = $user->currentAgency;

        // Get site templates with pages count
        $siteTemplates = \App\Models\GrowBuilder\SiteTemplate::where('is_active', true)
            ->with('pages')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'slug' => $template->slug,
                    'description' => $template->description,
                    'industry' => $template->industry,
                    'thumbnail' => $template->thumbnail_url,
                    'theme' => $template->theme,
                    'isPremium' => $template->is_premium,
                    'pagesCount' => $template->pages->count(),
                    'pages' => $template->pages->map(function ($page) {
                        return [
                            'title' => $page->title,
                            'slug' => $page->slug,
                            'isHomepage' => $page->is_homepage,
                        ];
                    }),
                ];
            });

        // Format templates for the component (maps to Template interface)
        $templates = $siteTemplates->map(function ($template) {
            return [
                'id' => $template['id'],
                'name' => $template['name'],
                'slug' => $template['slug'],
                'category' => $template['industry'] ?? 'general',
                'categoryLabel' => ucfirst($template['industry'] ?? 'General'),
                'description' => $template['description'],
                'thumbnail' => $template['thumbnail'],
                'previewImage' => $template['thumbnail'],
                'isPremium' => $template['isPremium'],
                'price' => $template['isPremium'] ? 0 : 0, // TODO: Add pricing if needed
            ];
        });

        // Get industries
        $industries = \App\Models\GrowBuilder\SiteTemplate::select('industry')
            ->distinct()
            ->whereNotNull('industry')
            ->where('is_active', true)
            ->orderBy('industry')
            ->pluck('industry')
            ->map(function ($industry) {
                return [
                    'slug' => \Str::slug($industry),
                    'name' => $industry,
                    'icon' => 'BuildingOfficeIcon', // Default icon
                ];
            });

        $data = [
            'templates' => $templates->values()->toArray(),
            'siteTemplates' => $siteTemplates->values()->toArray(),
            'industries' => $industries->values()->toArray(),
        ];

        // Add agency-specific data if user has an agency
        if ($agency) {
            // Get clients for the agency
            $clients = $agency->clients()
                ->where('status', 'active')
                ->orderBy('client_name')
                ->get()
                ->map(function ($client) {
                    return [
                        'id' => $client->id,
                        'name' => $client->client_name,
                        'company' => $client->company_name,
                        'type' => $client->client_type,
                    ];
                });

            // Check if client_id is provided in query params
            $selectedClientId = $request->query('client_id');

            $data['clients'] = $clients;
            $data['selectedClientId'] = $selectedClientId;
            $data['agency'] = [
                'id' => $agency->id,
                'name' => $agency->agency_name,
                'sitesUsed' => $agency->sites_used,
                'siteLimit' => $agency->site_limit,
                'canCreateSite' => $agency->sites_used < $agency->site_limit,
            ];
        }

        return Inertia::render('GrowBuilder/Sites/Create', $data);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $agency = $user->currentAgency;

        if (!$agency) {
            return redirect()->route('growbuilder.agency.dashboard')
                ->with('error', 'You need to create an agency first.');
        }

        // Log the incoming request for debugging
        \Log::info('Site creation attempt', [
            'user_id' => $user->id,
            'agency_id' => $agency->id,
            'request_data' => $request->all(),
        ]);

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:63|unique:growbuilder_sites,subdomain',
            'description' => 'nullable|string|max:1000',
            'site_template_id' => 'nullable|integer',
            'client_id' => 'nullable|integer|exists:agency_clients,id',
        ]);

        \Log::info('Site creation validation passed', ['validated_data' => $validated]);

        try {
            \DB::beginTransaction();
            
            // Create the site
            $site = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::create([
                'user_id' => $user->id,
                'client_id' => $validated['client_id'] ?? null,
                'site_template_id' => $validated['site_template_id'] ?? null,
                'name' => $validated['name'],
                'subdomain' => $validated['subdomain'],
                'description' => $validated['description'] ?? null,
                'status' => 'draft',
                'onboarding_completed' => false,
                'storage_used' => 0,
                'storage_limit' => 104857600, // 100MB default
            ]);

            \Log::info('Site created successfully', ['site_id' => $site->id]);

            // If a template was selected, copy template settings and theme
            if ($validated['site_template_id']) {
                $template = \App\Models\GrowBuilder\SiteTemplate::with('pages')->find($validated['site_template_id']);
                if ($template) {
                    // Copy template theme and settings to the site
                    $updateData = [];
                    
                    if ($template->theme) {
                        $updateData['theme'] = $template->theme;
                    }
                    
                    // Copy all settings including navigation and footer
                    if ($template->settings) {
                        $updateData['settings'] = $template->settings;
                    }
                    
                    if (!empty($updateData)) {
                        $site->update($updateData);
                        \Log::info('Template theme and settings copied', [
                            'template_id' => $template->id,
                            'copied_data' => $updateData
                        ]);
                    }

                    // Copy template pages to the site and collect nav links
                    $navLinks = [];
                    foreach ($template->pages as $templatePage) {
                        // Log the template page content for debugging
                        \Log::info('Processing template page', [
                            'page_title' => $templatePage->title,
                            'page_slug' => $templatePage->slug,
                            'content_type' => gettype($templatePage->content),
                            'content_preview' => is_array($templatePage->content) ? 
                                'Array with ' . count($templatePage->content) . ' items' : 
                                (is_string($templatePage->content) ? substr($templatePage->content, 0, 100) : 'null/other'),
                        ]);

                        // Ensure content is properly formatted as JSON
                        $content = $templatePage->content;
                        if (is_array($content)) {
                            $contentJson = $content;
                        } else if (is_string($content)) {
                            $contentJson = json_decode($content, true) ?? [];
                        } else {
                            $contentJson = [];
                        }

                        \App\Infrastructure\GrowBuilder\Models\GrowBuilderPage::create([
                            'site_id' => $site->id,
                            'title' => $templatePage->title,
                            'slug' => $templatePage->slug,
                            'content_json' => $contentJson,
                            'meta_title' => $templatePage->meta_title ?? $templatePage->title,
                            'meta_description' => $templatePage->meta_description ?? null,
                            'is_homepage' => $templatePage->is_homepage,
                            'is_published' => true,
                            'nav_order' => $templatePage->sort_order ?? 0,
                            'show_in_nav' => $templatePage->show_in_nav ?? true,
                        ]);
                        
                        // Add to nav links if it should show in nav
                        if ($templatePage->show_in_nav ?? true) {
                            $navLinks[] = [
                                'id' => 'nav-' . $templatePage->slug,
                                'label' => $templatePage->title,
                                'url' => $templatePage->is_homepage ? '/' : '/' . $templatePage->slug,
                                'isExternal' => false,
                                'children' => [],
                            ];
                        }
                    }
                    
                    // Generate footer columns from pages if template doesn't have them
                    $settings = $site->settings ?? [];
                    $settings['footer'] = $settings['footer'] ?? [];
                    
                    if (empty($settings['footer']['columns']) && !empty($navLinks)) {
                        // Split nav links into multiple columns for better layout
                        $infoLinks = array_filter($navLinks, fn($link) => 
                            in_array(strtolower($link['label']), ['home', 'about', 'contact'])
                        );
                        
                        $serviceLinks = array_filter($navLinks, fn($link) => 
                            in_array(strtolower($link['label']), ['services', 'ministries', 'programs', 'products'])
                        );
                        
                        $otherLinks = array_filter($navLinks, fn($link) => 
                            !in_array(strtolower($link['label']), ['home', 'about', 'contact', 'services', 'ministries', 'programs', 'products'])
                        );
                        
                        $columns = [];
                        
                        if (!empty($infoLinks)) {
                            $columns[] = [
                                'id' => 'footer-col-info',
                                'title' => 'Quick Links',
                                'links' => array_values($infoLinks),
                            ];
                        }
                        
                        if (!empty($serviceLinks)) {
                            $columns[] = [
                                'id' => 'footer-col-services',
                                'title' => 'Our Services',
                                'links' => array_values($serviceLinks),
                            ];
                        }
                        
                        if (!empty($otherLinks)) {
                            $columns[] = [
                                'id' => 'footer-col-other',
                                'title' => 'More',
                                'links' => array_values($otherLinks),
                            ];
                        }
                        
                        // If we have few links, just use one column
                        if (count($navLinks) <= 4) {
                            $columns = [
                                [
                                    'id' => 'footer-col-main',
                                    'title' => 'Quick Links',
                                    'links' => $navLinks,
                                ],
                            ];
                        }
                        
                        $settings['footer']['columns'] = $columns;
                        $site->settings = $settings;
                        $site->save();
                        
                        \Log::info('Generated footer columns from pages', ['columns_count' => count($columns)]);
                    }
                    
                    \Log::info('Template pages copied', ['template_id' => $template->id, 'pages_count' => $template->pages->count()]);
                } else {
                    \Log::warning('Template not found', ['template_id' => $validated['site_template_id']]);
                }
            } else {
                // Create a default homepage if no template was selected
                \App\Infrastructure\GrowBuilder\Models\GrowBuilderPage::create([
                    'site_id' => $site->id,
                    'title' => 'Home',
                    'slug' => 'home',
                    'content_json' => '[]', // Empty JSON array for blank page
                    'meta_title' => $validated['name'] . ' - Home',
                    'meta_description' => $validated['description'] ?? 'Welcome to ' . $validated['name'],
                    'is_homepage' => true,
                    'is_published' => true,
                    'nav_order' => 0,
                    'show_in_nav' => true,
                ]);
                \Log::info('Default homepage created for blank site');
            }

            // Increment agency sites_used counter
            $agency->increment('sites_used');

            // Commit the transaction before redirecting
            \DB::commit();

            // Refresh the site to ensure all updates are loaded
            $site->refresh();

            \Log::info('Site creation completed, redirecting to editor', [
                'site_id' => $site->id,
                'has_settings' => !empty($site->settings),
                'has_theme' => !empty($site->theme),
                'settings_data' => $site->settings,
            ]);

            return redirect()->route('growbuilder.editor', $site->id)
                ->with('success', 'Site created successfully!');
        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error('Site creation failed', [
                'user_id' => $user->id,
                'agency_id' => $agency->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to create site: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Request $request, int $id)
    {
        $user = $request->user();

        $site = $this->siteRepository->findByIdForUser(SiteId::fromInt($id), $user->id);
        if (!$site) {
            abort(404);
        }

        $pageViews = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
            ->where('viewed_date', '>=', now()->subDays(30))
            ->count();

        $eloquentSite = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::find($id);

        return Inertia::render('GrowBuilder/Sites/Show', [
            'site' => [
                'id' => $site->getId()->value(),
                'name' => $site->getName(),
                'subdomain' => $site->getSubdomain()->value(),
                'customDomain' => $site->getCustomDomain(),
                'description' => $site->getDescription(),
                'status' => $site->getStatus()->value(),
                'url' => $site->getSubdomain()->toUrl(),
                'logo' => $site->getLogo(),
                'favicon' => $site->getFavicon(),
                'isPublished' => $site->isPublished(),
                'onboardingCompleted' => $eloquentSite?->onboarding_completed ?? false,
                'createdAt' => $site->getCreatedAt()->format('M d, Y'),
                'updatedAt' => $site->getUpdatedAt()->format('Y-m-d H:i:s'),
                'publishedAt' => $site->getPublishedAt()?->format('M d, Y'),
                'storageUsed' => $eloquentSite?->storage_used ?? 0,
                'storageLimit' => $eloquentSite?->storage_limit ?? 104857600,
                'storageUsedFormatted' => $eloquentSite?->storage_used_formatted ?? '0 B',
                'storageLimitFormatted' => $eloquentSite?->storage_limit_formatted ?? '100 MB',
                'storagePercentage' => $eloquentSite?->storage_percentage ?? 0,
                'client' => $eloquentSite?->client ? [
                    'id' => $eloquentSite->client->id,
                    'name' => $eloquentSite->client->client_name,
                    'company' => $eloquentSite->client->company_name,
                    'type' => $eloquentSite->client->client_type,
                ] : null,
            ],
            'stats' => [
                'pageViews' => $pageViews,
                'totalPages' => $eloquentSite?->pages->count() ?? 0,
                'totalMedia' => $eloquentSite?->media->count() ?? 0,
                'visitors' => 0,
            ],
        ]);
    }

    public function update(Request $request, int $id)
    {
        return response()->json(['message' => 'Update method - under development']);
    }

    public function destroy(Request $request, int $id)
    {
        return response()->json(['message' => 'Destroy method - under development']);
    }

    public function publish(Request $request, int $id)
    {
        $site = $this->siteRepository->findByIdForUser(SiteId::fromInt($id), $request->user()->id);
        if (!$site) {
            abort(404);
        }
        $site->publish();
        $this->siteRepository->save($site);
        return response()->json(['message' => 'Site published successfully.']);
    }

    public function unpublish(Request $request, int $id)
    {
        $site = $this->siteRepository->findByIdForUser(SiteId::fromInt($id), $request->user()->id);
        if (!$site) {
            abort(404);
        }
        $site->unpublish();
        $this->siteRepository->save($site);
        return response()->json(['message' => 'Site unpublished.']);
    }

    public function batchUpdate(Request $request)
    {
        $validated = $request->validate([
            'site_ids' => 'required|array|min:1',
            'site_ids.*' => 'integer|exists:growbuilder_sites,id',
            'action' => 'required|in:publish,unpublish,delete',
        ]);

        $user = $request->user();
        $affected = 0;

        foreach ($validated['site_ids'] as $id) {
            $site = $this->siteRepository->findByIdForUser(SiteId::fromInt($id), $user->id);
            if (!$site) {
                continue;
            }

            match ($validated['action']) {
                'publish' => $site->publish(),
                'unpublish' => $site->unpublish(),
                'delete' => $this->siteRepository->delete(SiteId::fromInt($id)),
            };

            if ($validated['action'] !== 'delete') {
                $this->siteRepository->save($site);
            }
            $affected++;
        }

        $verb = match ($validated['action']) {
            'publish' => 'published',
            'unpublish' => 'unpublished',
            'delete' => 'deleted',
        };

        return response()->json(['message' => "{$affected} site(s) {$verb} successfully.", 'affected' => $affected]);
    }

    public function settings(Request $request, int $id)
    {
        $user = $request->user();

        $siteEntity = $this->siteRepository->findByIdForUser(SiteId::fromInt($id), $user->id);
        if (!$siteEntity) {
            abort(404);
        }

        $site = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::with(['pages', 'client'])->findOrFail($id);

        $siteData = [
            'id' => $site->id,
            'name' => $site->name,
            'subdomain' => $site->slug,
            'customDomain' => $site->custom_domain,
            'description' => $site->description,
            'status' => $site->is_published ? 'published' : 'draft',
            'plan' => $siteEntity->getPlan()->value(),
            'logo' => $site->logo,
            'favicon' => $site->favicon,
            'theme' => $site->theme ?? null,
            'seoSettings' => [
                'metaTitle' => $site->seo_title,
                'metaDescription' => $site->seo_description,
                'ogImage' => $site->og_image ?? null,
                'googleAnalyticsId' => $site->google_analytics_id,
            ],
            'socialLinks' => $site->social_links ?? null,
            'contactInfo' => [
                'phone' => $site->contact_phone,
                'email' => $site->contact_email,
                'address' => $site->address,
            ],
            'settings' => $site->settings ?? null,
        ];

        return Inertia::render('GrowBuilder/Sites/Settings', [
            'site' => $siteData,
        ]);
    }

    public function analytics(Request $request, int $id)
    {
        $user = $request->user();

        $site = $this->siteRepository->findByIdForUser(SiteId::fromInt($id), $user->id);
        if (!$site) {
            abort(404);
        }

        $period = $request->get('period', '30d');
        $days = match($period) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 30,
        };

        $totalViews = $this->analyticsService->getTotalViews($id, $days);
        $totalVisitors = $this->analyticsService->getTotalVisitors($id, $days);
        $previousPeriodViews = $this->analyticsService->getPreviousPeriodViews($id, $days);

        $viewsChange = $previousPeriodViews > 0
            ? round((($totalViews - $previousPeriodViews) / $previousPeriodViews) * 100, 1)
            : 0;

        $dailyStats = $this->analyticsService->getDailyStats($id, $days);
        $deviceStats = $this->analyticsService->getDeviceStats($id, $days, $totalViews);
        $topPages = $this->analyticsService->getTopPages($id, $days);
        $trafficSources = $this->analyticsService->getTrafficSources($id, $days, $totalVisitors);
        $geographicData = $this->analyticsService->getGeographicData($id, $days, $totalVisitors);

        return Inertia::render('GrowBuilder/Sites/Analytics', [
            'site' => [
                'id' => $site->getId()->value(),
                'name' => $site->getName(),
                'subdomain' => $site->getSubdomain()->value(),
            ],
            'totalViews' => $totalViews,
            'totalVisitors' => $totalVisitors,
            'viewsChange' => $viewsChange,
            'avgSessionDuration' => 0,
            'newVisitors' => $totalVisitors,
            'returningVisitors' => 0,
            'dailyStats' => $dailyStats,
            'deviceStats' => $deviceStats,
            'topPages' => $topPages,
            'trafficSources' => $trafficSources,
            'geographicData' => $geographicData,
            'conversionGoals' => [],
            'period' => $period,
        ]);
    }

    public function exportAnalytics(Request $request, int $id)
    {
        return response()->json(['message' => 'Export analytics method - under development']);
    }

    public function messages(Request $request, int $id)
    {
        $site = $this->siteRepository->findByIdForUser(SiteId::fromInt($id), $request->user()->id);
        if (!$site) {
            abort(404);
        }

        $perPage = $request->input('per_page', 20);
        $status = $request->input('status');
        $search = $request->input('search');

        $query = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::forSite($id);

        if ($status && in_array($status, ['unread', 'read', 'replied', 'archived'])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($messages);
    }

    public function showMessage(Request $request, int $id, int $messageId)
    {
        $site = $this->siteRepository->findByIdForUser(SiteId::fromInt($id), $request->user()->id);
        if (!$site) {
            abort(404);
        }
        $message = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::forSite($id)->findOrFail($messageId);
        $message->markAsRead();
        return response()->json($message->load('site'));
    }

    private function verifySiteOwnership(int $siteId, int $userId): void
    {
        $site = $this->siteRepository->findByIdForUser(SiteId::fromInt($siteId), $userId);
        abort_unless($site, 404);
    }

    public function replyMessage(Request $request, int $id, int $messageId)
    {
        $validated = $request->validate([
            'reply' => 'required|string|max:5000',
        ]);

        $this->verifySiteOwnership($id, $request->user()->id);
        $message = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::forSite($id)->findOrFail($messageId);
        $message->update([
            'reply' => $validated['reply'],
            'status' => 'replied',
            'replied_at' => now(),
            'replied_by' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Reply sent successfully.', 'data' => $message->fresh()->load('site')]);
    }

    public function updateMessageStatus(Request $request, int $id, int $messageId)
    {
        $validated = $request->validate([
            'status' => 'required|in:unread,read,replied,archived',
        ]);

        $this->verifySiteOwnership($id, $request->user()->id);
        $message = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::forSite($id)->findOrFail($messageId);
        $message->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Message status updated.', 'data' => $message->fresh()]);
    }

    public function deleteMessage(Request $request, int $id, int $messageId)
    {
        $this->verifySiteOwnership($id, $request->user()->id);
        $message = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::forSite($id)->findOrFail($messageId);
        $message->delete();

        return response()->json(['message' => 'Message deleted.']);
    }

    public function exportMessages(Request $request, int $id)
    {
        $this->verifySiteOwnership($id, $request->user()->id);

        $messages = \App\Infrastructure\GrowBuilder\Models\SiteContactMessage::forSite($id)
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = ['Name', 'Email', 'Phone', 'Subject', 'Message', 'Status', 'Reply', 'Date'];
        $rows = $messages->map(fn($m) => [
            $m->name, $m->email, $m->phone ?? '', $m->subject ?? '',
            $m->message, $m->status, $m->reply ?? '', $m->created_at->toDateTimeString(),
        ]);

        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM for Excel UTF-8
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"messages-{$id}.csv\"",
        ]);
    }

    public function completeOnboarding(Request $request, int $id)
    {
        return response()->json(['message' => 'Complete onboarding method - under development']);
    }

    public function users(Request $request, int $id)
    {
        return response()->json(['message' => 'Users method - under development']);
    }

    public function createUser(Request $request, int $id)
    {
        return response()->json(['message' => 'Create user method - under development']);
    }

    public function updateUserRole(Request $request, int $id, int $userId)
    {
        return response()->json(['message' => 'Update user role method - under development']);
    }

    public function deleteUser(Request $request, int $id, int $userId)
    {
        return response()->json(['message' => 'Delete user method - under development']);
    }

    public function roles(Request $request, int $id)
    {
        return response()->json(['message' => 'Roles method - under development']);
    }

    public function permissions(Request $request, int $id)
    {
        return response()->json(['message' => 'Permissions method - under development']);
    }

    public function createRole(Request $request, int $id)
    {
        return response()->json(['message' => 'Create role method - under development']);
    }

    public function updateRole(Request $request, int $id, int $roleId)
    {
        return response()->json(['message' => 'Update role method - under development']);
    }

    public function deleteRole(Request $request, int $id, int $roleId)
    {
        return response()->json(['message' => 'Delete role method - under development']);
    }

    public function restore(Request $request, int $id)
    {
        return response()->json(['message' => 'Restore method - under development']);
    }

    public function preview(Request $request, string $subdomain, ?string $page = null)
    {
        return response()->json(['message' => 'Preview method - under development']);
    }

    public function switchTier(Request $request)
    {
        $user = $request->user();
        
        // Only allow admins to switch tiers
        if (!$user->hasRole(['Administrator', 'admin', 'superadmin'])) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $validated = $request->validate([
            'tier' => 'required|string|in:free,starter,business,agency',
        ]);

        try {
            // Get or create user's module subscription
            $subscription = \App\Models\ModuleSubscription::firstOrCreate([
                'user_id' => $user->id,
                'module_id' => 'growbuilder',
            ], [
                'subscription_tier' => $validated['tier'],
                'status' => 'active',
                'started_at' => now(),
                'expires_at' => now()->addYear(),
                'auto_renew' => true,
                'billing_cycle' => 'monthly',
                'amount' => 0, // Free for admin testing
                'currency' => 'ZMW',
            ]);

            // Update the tier
            $subscription->update([
                'subscription_tier' => $validated['tier'],
                'status' => 'active',
                'expires_at' => now()->addYear(),
            ]);

            // Clear any cached subscription data
            $subscriptionService = app(\App\Domain\Module\Services\SubscriptionService::class);
            $subscriptionService->clearCache($user, 'growbuilder');

            return redirect()->back()->with('success', 'Tier switched successfully!');
        } catch (\Exception $e) {
            \Log::error('Tier switch failed', [
                'user_id' => $user->id,
                'tier' => $validated['tier'],
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to switch tier');
        }
    }

    public function showProduct(Request $request, string $subdomain, string $slug)
    {
        return response()->json(['message' => 'Show product method - under development']);
    }

    public function checkout(Request $request, string $subdomain)
    {
        return response()->json(['message' => 'Checkout method - under development']);
    }
}