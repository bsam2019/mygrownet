<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SiteController extends Controller
{
    private const MODULE_ID = 'growbuilder';

    public function __construct()
    {
        // Dependencies commented out for now
    }

    // DEBUG: Simple test method
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
        // DEBUG: Log that we're hitting this method
        \Log::info('GrowBuilder SiteController@index hit', [
            'user_id' => $request->user()?->id,
            'user_email' => $request->user()?->email,
            'is_admin' => $request->user()?->hasRole(['Administrator', 'admin', 'superadmin']),
            'url' => $request->url(),
            'route_name' => $request->route()?->getName(),
            'middleware' => $request->route()?->middleware(),
        ]);

        $user = $request->user();
        
        // Get user's sites
        $sites = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::byUser($user->id)
            ->with(['pages', 'media', 'client'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get basic stats with actual analytics data
        $totalPageViews = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::whereIn('site_id', $sites->pluck('id'))->count();
        $totalOrders = 0; // TODO: Add when orders model is available
        $totalRevenue = 0; // TODO: Add when orders model is available
        $totalMessages = 0; // TODO: Add when contact messages model is available
        $unreadMessages = 0; // TODO: Add when contact messages model is available

        $stats = [
            'totalSites' => $sites->count(),
            'publishedSites' => $sites->where('status', 'published')->count(),
            'totalPageViews' => $totalPageViews,
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'totalMessages' => $totalMessages,
            'unreadMessages' => $unreadMessages,
        ];

        // Get page views per site for individual site stats
        $pageViewsPerSite = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::whereIn('site_id', $sites->pluck('id'))
            ->selectRaw('site_id, COUNT(*) as views')
            ->groupBy('site_id')
            ->pluck('views', 'site_id')
            ->toArray();

        // Format sites for frontend
        $sitesData = $sites->map(function ($site) use ($pageViewsPerSite) {
            return [
                'id' => $site->id,
                'name' => $site->name,
                'subdomain' => $site->subdomain,
                'customDomain' => $site->custom_domain,
                'description' => $site->description,
                'status' => $site->status,
                'url' => $site->url,
                'logo' => $site->logo,
                'favicon' => $site->favicon,
                'isPublished' => $site->isPublished(),
                'onboardingCompleted' => $site->onboarding_completed,
                'createdAt' => $site->created_at->diffForHumans(),
                'updatedAt' => $site->updated_at->diffForHumans(),
                'publishedAt' => $site->published_at?->diffForHumans(),
                'storageUsed' => $site->storage_used ?? 0,
                'storageLimit' => $site->storage_limit ?? 104857600, // 100MB default
                'storageUsedFormatted' => $site->storage_used_formatted,
                'storageLimitFormatted' => $site->storage_limit_formatted,
                'storagePercentage' => $site->storage_percentage,
                'pageCount' => $site->pages->count(),
                'mediaCount' => $site->media->count(),
                'pageViews' => $pageViewsPerSite[$site->id] ?? 0,
                'ordersCount' => 0, // TODO: Get actual orders
                'revenue' => 0, // TODO: Get actual revenue
                'messagesCount' => 0, // TODO: Get actual messages
                'unreadMessages' => 0, // TODO: Get unread messages
                'client' => $site->client ? [
                    'id' => $site->client->id,
                    'name' => $site->client->client_name,
                    'company' => $site->client->company_name,
                    'type' => $site->client->client_type,
                ] : null,
            ];
        });

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
                    'icon' => 'BuildingOfficeIcon',
                ];
            })->values()->toArray();

        return Inertia::render('GrowBuilder/Dashboard', [
            'sites' => $sitesData,
            'stats' => $stats,
            'subscription' => $subscription,
            'availableTiers' => $availableTiers,
            'isAdmin' => $user->hasRole(['Administrator', 'admin', 'superadmin']),
            'clients' => $this->getClientsForUser($user),
            'siteTemplates' => $siteTemplates,
            'industries' => $industries,
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
        
        // Get the site
        $site = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['pages', 'media', 'client'])
            ->firstOrFail();

        // Get site statistics
        $pageViews = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
            ->where('viewed_date', '>=', now()->subDays(30))
            ->count();

        $totalPages = $site->pages->count();
        $totalMedia = $site->media->count();

        return Inertia::render('GrowBuilder/Sites/Show', [
            'site' => [
                'id' => $site->id,
                'name' => $site->name,
                'subdomain' => $site->subdomain,
                'customDomain' => $site->custom_domain,
                'description' => $site->description,
                'status' => $site->status,
                'url' => $site->url,
                'logo' => $site->logo,
                'favicon' => $site->favicon,
                'isPublished' => $site->isPublished(),
                'onboardingCompleted' => $site->onboarding_completed,
                'createdAt' => $site->created_at->format('M d, Y'),
                'updatedAt' => $site->updated_at->diffForHumans(),
                'publishedAt' => $site->published_at?->format('M d, Y'),
                'storageUsed' => $site->storage_used ?? 0,
                'storageLimit' => $site->storage_limit ?? 104857600,
                'storageUsedFormatted' => $site->storage_used_formatted,
                'storageLimitFormatted' => $site->storage_limit_formatted,
                'storagePercentage' => $site->storage_percentage,
                'client' => $site->client ? [
                    'id' => $site->client->id,
                    'name' => $site->client->client_name,
                    'company' => $site->client->company_name,
                    'type' => $site->client->client_type,
                ] : null,
            ],
            'stats' => [
                'pageViews' => $pageViews,
                'totalPages' => $totalPages,
                'totalMedia' => $totalMedia,
                'visitors' => 0, // TODO: Calculate unique visitors
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
        return response()->json(['message' => 'Publish method - under development']);
    }

    public function unpublish(Request $request, int $id)
    {
        return response()->json(['message' => 'Unpublish method - under development']);
    }

    public function settings(Request $request, int $id)
    {
        $user = $request->user();

        // Get the site with relationships
        $site = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['pages', 'client'])
            ->firstOrFail();

        // Prepare site data
        $siteData = [
            'id' => $site->id,
            'name' => $site->name,
            'subdomain' => $site->slug,
            'customDomain' => $site->custom_domain,
            'description' => $site->description,
            'status' => $site->is_published ? 'published' : 'draft',
            'plan' => 'free', // TODO: Get actual plan
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

            // Get the site
            $site = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::where('id', $id)
                ->where('user_id', $user->id)
                ->with(['pages'])
                ->firstOrFail();

            // Get period from request (default 30 days)
            $period = $request->get('period', '30d');
            $days = match($period) {
                '7d' => 7,
                '30d' => 30,
                '90d' => 90,
                default => 30,
            };

            // Get total views and visitors (only count records with IP addresses for visitors)
            $totalViews = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
                ->where('viewed_date', '>=', now()->subDays($days))
                ->count();

            $totalVisitors = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
                ->where('viewed_date', '>=', now()->subDays($days))
                ->whereNotNull('ip_address')
                ->where('ip_address', '!=', '')
                ->distinct('ip_address')
                ->count();

            // Calculate change from previous period
            $previousPeriodViews = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
                ->where('viewed_date', '>=', now()->subDays($days * 2))
                ->where('viewed_date', '<', now()->subDays($days))
                ->count();

            $viewsChange = $previousPeriodViews > 0 
                ? round((($totalViews - $previousPeriodViews) / $previousPeriodViews) * 100, 1)
                : 0;

            // Get daily stats - fill missing dates with zeros for proper trend line
            $dailyStatsQuery = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
                ->where('viewed_date', '>=', now()->subDays($days))
                ->selectRaw('DATE(viewed_date) as date, COUNT(*) as views, COUNT(DISTINCT CASE WHEN ip_address IS NOT NULL AND ip_address != "" THEN ip_address END) as visitors')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            // Create complete date range with zeros for missing dates
            $dailyStats = collect();
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $stats = $dailyStatsQuery->get($date);

                $dailyStats->push([
                    'date' => $date,
                    'views' => $stats ? (int) $stats->views : 0,
                    'visitors' => $stats ? (int) $stats->visitors : 0,
                ]);
            }

            // Get device stats - only real data
            $deviceStats = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
                ->where('viewed_date', '>=', now()->subDays($days))
                ->selectRaw('COALESCE(NULLIF(device_type, ""), "Unknown") as device, COUNT(*) as count')
                ->groupBy('device')
                ->get()
                ->map(function ($item) use ($totalViews) {
                    return [
                        'device' => $item->device,
                        'count' => (int) $item->count,
                        'percentage' => $totalViews > 0 ? round(($item->count / $totalViews) * 100, 1) : 0,
                    ];
                });

            // Get top pages - only real data
            $topPages = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
                ->where('viewed_date', '>=', now()->subDays($days))
                ->selectRaw('COALESCE(NULLIF(path, ""), "/") as path, COUNT(*) as views')
                ->groupBy('path')
                ->orderByDesc('views')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'path' => $item->path,
                        'views' => (int) $item->views,
                        'avgTime' => 0, // We don't track this yet
                        'bounceRate' => 0, // We don't track this yet
                    ];
                });

            // Get traffic sources - only real data
            $trafficSources = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
                ->where('viewed_date', '>=', now()->subDays($days))
                ->whereNotNull('ip_address')
                ->where('ip_address', '!=', '')
                ->selectRaw('
                    CASE 
                        WHEN referrer IS NULL OR referrer = "" THEN "Direct"
                        WHEN referrer LIKE "%google%" THEN "Google"
                        WHEN referrer LIKE "%facebook%" THEN "Facebook"
                        WHEN referrer LIKE "%twitter%" THEN "Twitter"
                        ELSE "Other"
                    END as source,
                    COUNT(DISTINCT ip_address) as visitors
                ')
                ->groupBy('source')
                ->get()
                ->map(function ($item) use ($totalVisitors) {
                    $type = match(strtolower($item->source)) {
                        'direct' => 'direct',
                        'google' => 'search',
                        'facebook', 'twitter' => 'social',
                        default => 'referral',
                    };

                    return [
                        'source' => $item->source,
                        'visitors' => (int) $item->visitors,
                        'percentage' => $totalVisitors > 0 ? round(($item->visitors / $totalVisitors) * 100, 1) : 0,
                        'type' => $type,
                    ];
                });

            // Get geographic data - only real data, only count records with IP addresses
            $geographicData = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
                ->where('viewed_date', '>=', now()->subDays($days))
                ->whereNotNull('country')
                ->where('country', '!=', '')
                ->whereNotNull('ip_address')
                ->where('ip_address', '!=', '')
                ->selectRaw('country, COUNT(DISTINCT ip_address) as visitors')
                ->groupBy('country')
                ->orderByDesc('visitors')
                ->limit(10)
                ->get()
                ->map(function ($item) use ($totalVisitors) {
                    // Map country codes to proper names (basic mapping)
                    $countryNames = [
                        'US' => 'United States',
                        'GB' => 'United Kingdom', 
                        'CA' => 'Canada',
                        'DE' => 'Germany',
                        'FR' => 'France',
                        'AU' => 'Australia',
                        'NL' => 'Netherlands',
                        'ZM' => 'Zambia',
                        'ZA' => 'South Africa',
                        'KE' => 'Kenya',
                        'NG' => 'Nigeria',
                        'GH' => 'Ghana',
                    ];

                    $countryCode = strtoupper($item->country);
                    $countryName = $countryNames[$countryCode] ?? ucfirst(strtolower($item->country));

                    return [
                        'country' => $countryName,
                        'countryCode' => $countryCode,
                        'visitors' => (int) $item->visitors,
                        'percentage' => $totalVisitors > 0 ? round(($item->visitors / $totalVisitors) * 100, 1) : 0,
                    ];
                });

            // Calculate session metrics from available data
            $avgSessionDuration = 0; // We don't track session duration yet
            $newVisitorsCount = $totalVisitors; // Assume all are new since we don't track returning visitors yet
            $returningVisitorsCount = 0;

            return Inertia::render('GrowBuilder/Sites/Analytics', [
                'site' => [
                    'id' => $site->id,
                    'name' => $site->name,
                    'subdomain' => $site->subdomain,
                ],
                'totalViews' => $totalViews,
                'totalVisitors' => $totalVisitors,
                'viewsChange' => $viewsChange,
                'avgSessionDuration' => $avgSessionDuration,
                'newVisitors' => $newVisitorsCount,
                'returningVisitors' => $returningVisitorsCount,
                'dailyStats' => $dailyStats,
                'deviceStats' => $deviceStats,
                'topPages' => $topPages,
                'trafficSources' => $trafficSources,
                'geographicData' => $geographicData,
                'conversionGoals' => [], // TODO: Add conversion tracking
                'period' => $period,
            ]);
        }

    public function exportAnalytics(Request $request, int $id)
    {
        return response()->json(['message' => 'Export analytics method - under development']);
    }

    public function messages(Request $request, int $id)
    {
        return response()->json(['message' => 'Messages method - under development']);
    }

    public function showMessage(Request $request, int $id, int $messageId)
    {
        return response()->json(['message' => 'Show message method - under development']);
    }

    public function replyMessage(Request $request, int $id, int $messageId)
    {
        return response()->json(['message' => 'Reply message method - under development']);
    }

    public function updateMessageStatus(Request $request, int $id, int $messageId)
    {
        return response()->json(['message' => 'Update message status method - under development']);
    }

    public function deleteMessage(Request $request, int $id, int $messageId)
    {
        return response()->json(['message' => 'Delete message method - under development']);
    }

    public function exportMessages(Request $request, int $id)
    {
        return response()->json(['message' => 'Export messages method - under development']);
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