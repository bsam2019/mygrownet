<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Models\AgencyClient;
use App\Models\AgencyClientContact;
use App\Models\AgencyClientTag;
use App\Services\GrowBuilder\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function __construct(
        private ActivityLogger $activityLogger
    ) {}

    /**
     * Display list of clients
     */
    public function index(Request $request): Response
    {
        $agency = $request->user()->currentAgency;

        $query = AgencyClient::with(['primaryContact', 'tags', 'sites'])
            ->withCount('sites');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('client_type', $request->type);
        }

        // Filter by tag
        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('agency_client_tags.id', $request->tag);
            });
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('client_code', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $clients = $query->paginate(20)->through(function ($client) {
            return [
                'id' => $client->id,
                'client_code' => $client->client_code,
                'client_name' => $client->client_name,
                'company_name' => $client->company_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'client_type' => $client->client_type,
                'status' => $client->status,
                'onboarding_status' => $client->onboarding_status,
                'sites_count' => $client->sites_count,
                'primary_contact' => $client->primaryContact ? [
                    'name' => $client->primaryContact->full_name,
                    'email' => $client->primaryContact->email,
                ] : null,
                'tags' => $client->tags->map(fn($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'color' => $tag->color,
                ]),
                'created_at' => $client->created_at->format('M d, Y'),
            ];
        });

        // Get available tags for filtering
        $availableTags = AgencyClientTag::select('id', 'name', 'color')->get();

        return Inertia::render('GrowBuilder/Clients/Index', [
            'clients' => $clients,
            'filters' => [
                'status' => $request->get('status', 'all'),
                'type' => $request->get('type', 'all'),
                'tag' => $request->get('tag'),
                'search' => $request->get('search'),
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
            'available_tags' => $availableTags,
            'stats' => [
                'total' => AgencyClient::count(),
                'active' => AgencyClient::where('status', 'active')->count(),
                'leads' => AgencyClient::where('status', 'lead')->count(),
                'suspended' => AgencyClient::where('status', 'suspended')->count(),
            ],
        ]);
    }

    /**
     * Show create client form
     */
    public function create(): Response
    {
        $availableTags = AgencyClientTag::select('id', 'name', 'color')->get();

        return Inertia::render('GrowBuilder/Clients/Create', [
            'available_tags' => $availableTags,
        ]);
    }

    /**
     * Store new client
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'alternative_phone' => 'nullable|string|max:50',
            'client_type' => 'required|in:individual,business,institution,church,school,ngo,government',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:2',
            'status' => 'nullable|in:lead,active,suspended,cancelled,archived',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:agency_client_tags,id',
            'contacts' => 'nullable|array',
            'contacts.*.full_name' => 'required|string|max:255',
            'contacts.*.email' => 'required|email|max:255',
            'contacts.*.phone' => 'nullable|string|max:50',
            'contacts.*.role_title' => 'nullable|string|max:255',
            'contacts.*.is_primary' => 'boolean',
        ]);

        $client = AgencyClient::create($validated);

        // Attach tags
        if (!empty($validated['tags'])) {
            $client->tags()->attach($validated['tags']);
        }

        // Create contacts
        if (!empty($validated['contacts'])) {
            foreach ($validated['contacts'] as $contactData) {
                $client->contacts()->create($contactData);
            }
        }

        // Log activity
        $this->activityLogger->logClientCreated($client);

        return redirect()->route('growbuilder.clients.show', $client->id)
            ->with('success', 'Client created successfully.');
    }

    /**
     * Show client details
     */
    public function show(Request $request, int $id): Response
    {
        $client = AgencyClient::with(['contacts', 'tags', 'sites.pages'])
            ->withCount('sites')
            ->findOrFail($id);

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

        // Check if user has GrowBuilder subscription (agency users have access)
        $hasGrowBuilderSubscription = $client->agency_id ? true : false;

        return Inertia::render('GrowBuilder/Clients/Show', [
            'client' => [
                'id' => $client->id,
                'client_code' => $client->client_code,
                'client_name' => $client->client_name,
                'company_name' => $client->company_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'alternative_phone' => $client->alternative_phone,
                'client_type' => $client->client_type,
                'address' => $client->address,
                'city' => $client->city,
                'country' => $client->country,
                'status' => $client->status,
                'onboarding_status' => $client->onboarding_status,
                'notes' => $client->notes,
                'created_at' => $client->created_at->format('M d, Y'),
                'contacts' => $client->contacts->map(fn($contact) => [
                    'id' => $contact->id,
                    'full_name' => $contact->full_name,
                    'email' => $contact->email,
                    'phone' => $contact->phone,
                    'role_title' => $contact->role_title,
                    'is_primary' => $contact->is_primary,
                ]),
                'tags' => $client->tags->map(fn($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'color' => $tag->color,
                ]),
                'sites' => $client->sites->map(fn($site) => [
                    'id' => $site->id,
                    'site_name' => $site->name,
                    'subdomain' => $site->subdomain,
                    'custom_domain' => $site->custom_domain,
                    'site_status' => $site->status,
                    'storage_used_mb' => $site->storage_used ? round($site->storage_used / 1024 / 1024, 2) : 0,
                    'pages_count' => $site->pages->count(),
                    'preview_url' => $site->url,
                    'created_at' => $site->created_at->format('M d, Y'),
                ]),
                'sites_count' => $client->sites_count,
                'total_storage_mb' => $client->sites->sum(fn($site) => $site->storage_used ? round($site->storage_used / 1024 / 1024, 2) : 0),
                'services_count' => 0, // TODO: Add services count when implemented
                'billing_status' => 'active', // TODO: Get actual billing status
            ],
            'siteTemplates' => $siteTemplates,
            'industries' => $industries,
            'hasGrowBuilderSubscription' => $hasGrowBuilderSubscription,
        ]);
    }

    /**
     * Show edit client form
     */
    public function edit(int $id): Response
    {
        $client = AgencyClient::with(['contacts', 'tags'])->findOrFail($id);
        $availableTags = AgencyClientTag::select('id', 'name', 'color')->get();

        return Inertia::render('GrowBuilder/Clients/Edit', [
            'client' => [
                'id' => $client->id,
                'client_code' => $client->client_code,
                'client_name' => $client->client_name,
                'company_name' => $client->company_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'alternative_phone' => $client->alternative_phone,
                'client_type' => $client->client_type,
                'address' => $client->address,
                'city' => $client->city,
                'country' => $client->country,
                'status' => $client->status,
                'onboarding_status' => $client->onboarding_status,
                'notes' => $client->notes,
                'contacts' => $client->contacts,
                'tags' => $client->tags->pluck('id'),
            ],
            'available_tags' => $availableTags,
        ]);
    }

    /**
     * Update client
     */
    public function update(Request $request, int $id)
    {
        $client = AgencyClient::findOrFail($id);

        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'alternative_phone' => 'nullable|string|max:50',
            'client_type' => 'required|in:individual,business,institution,church,school,ngo,government',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:2',
            'status' => 'nullable|in:lead,active,suspended,cancelled,archived',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:agency_client_tags,id',
        ]);

        $client->update($validated);

        // Sync tags
        if (isset($validated['tags'])) {
            $client->tags()->sync($validated['tags']);
        }

        // Log activity
        $this->activityLogger->logClientUpdated($client);

        return redirect()->route('growbuilder.clients.show', $client->id)
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Delete client
     */
    public function destroy(int $id)
    {
        $client = AgencyClient::findOrFail($id);
        
        // Check if client has sites
        if ($client->sites()->count() > 0) {
            return back()->with('error', 'Cannot delete client with active sites.');
        }

        $clientName = $client->client_name;
        $client->delete();

        return redirect()->route('growbuilder.clients.index')
            ->with('success', "Client '{$clientName}' deleted successfully.");
    }

    /**
     * Suspend all client sites
     */
    public function suspendSites(int $id)
    {
        $client = AgencyClient::with('sites')->findOrFail($id);
        
        $suspendedCount = 0;
        foreach ($client->sites as $site) {
            if ($site->status !== 'suspended') {
                $site->update(['status' => 'suspended']);
                $suspendedCount++;
            }
        }

        // Log activity
        $this->activityLogger->log(
            $client->agency_id,
            'client_sites_suspended',
            'client',
            $client->id,
            "Suspended {$suspendedCount} sites for client: {$client->client_name}"
        );

        return back()->with('success', "Suspended {$suspendedCount} site(s) for {$client->client_name}.");
    }

    /**
     * Activate all client sites
     */
    public function activateSites(int $id)
    {
        $client = AgencyClient::with('sites')->findOrFail($id);
        
        $activatedCount = 0;
        foreach ($client->sites as $site) {
            if ($site->status === 'suspended') {
                $site->update(['status' => 'active']);
                $activatedCount++;
            }
        }

        // Log activity
        $this->activityLogger->log(
            $client->agency_id,
            'client_sites_activated',
            'client',
            $client->id,
            "Activated {$activatedCount} sites for client: {$client->client_name}"
        );

        return back()->with('success', "Activated {$activatedCount} site(s) for {$client->client_name}.");
    }

    /**
     * Get client analytics and reports
     */
    public function analytics(Request $request, int $id): Response
    {
        $client = AgencyClient::with(['sites', 'tags'])->findOrFail($id);

        // Calculate storage usage per site
        $storageBysite = $client->sites->map(function ($site) {
            return [
                'site_name' => $site->name,
                'storage_mb' => $site->storage_used ? round($site->storage_used / 1024 / 1024, 2) : 0,
            ];
        })->sortByDesc('storage_mb')->values();

        // Calculate pages per site
        $pagesBySite = $client->sites->map(function ($site) {
            return [
                'site_name' => $site->name,
                'pages_count' => $site->pages()->count(),
            ];
        })->sortByDesc('pages_count')->values();

        // Site status distribution
        $sitesByStatus = $client->sites->groupBy('status')->map(function ($sites, $status) {
            return [
                'status' => $status,
                'count' => $sites->count(),
            ];
        })->values();

        // Timeline data (sites created over time)
        $sitesTimeline = $client->sites->groupBy(function ($site) {
            return $site->created_at->format('Y-m');
        })->map(function ($sites, $month) {
            return [
                'month' => $month,
                'count' => $sites->count(),
            ];
        })->values();

        // Total statistics
        $stats = [
            'total_sites' => $client->sites->count(),
            'active_sites' => $client->sites->where('status', 'active')->count(),
            'suspended_sites' => $client->sites->where('status', 'suspended')->count(),
            'total_storage_mb' => $client->sites->sum(fn($site) => $site->storage_used ? round($site->storage_used / 1024 / 1024, 2) : 0),
            'total_pages' => $client->sites->sum(fn($site) => $site->pages()->count()),
            'avg_pages_per_site' => $client->sites->count() > 0 
                ? round($client->sites->sum(fn($site) => $site->pages()->count()) / $client->sites->count(), 1) 
                : 0,
        ];

        return Inertia::render('GrowBuilder/Clients/Analytics', [
            'client' => [
                'id' => $client->id,
                'client_code' => $client->client_code,
                'client_name' => $client->client_name,
                'company_name' => $client->company_name,
                'status' => $client->status,
            ],
            'stats' => $stats,
            'storage_by_site' => $storageBysite,
            'pages_by_site' => $pagesBySite,
            'sites_by_status' => $sitesByStatus,
            'sites_timeline' => $sitesTimeline,
        ]);
    }
}
