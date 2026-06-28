<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Models\AgencyClient;
use App\Models\AgencyClientService;
use App\Services\GrowBuilder\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function __construct(
        private ActivityLogger $activityLogger
    ) {}

    /**
     * Display list of services
     */
    public function index(Request $request): Response
    {
        $query = AgencyClientService::with(['client', 'site'])
            ->orderBy('created_at', 'desc');

        // Filter by client
        if ($request->has('client_id') && $request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by service type
        if ($request->has('service_type') && $request->service_type !== 'all') {
            $query->where('service_type', $request->service_type);
        }

        $services = $query->paginate(20)->through(function ($service) {
            return [
                'id' => $service->id,
                'service_name' => $service->service_name,
                'service_type' => $service->service_type,
                'client' => [
                    'id' => $service->client->id,
                    'name' => $service->client->client_name,
                    'company_name' => $service->client->company_name,
                ],
                'billing_model' => $service->billing_model,
                'unit_price' => (float) $service->unit_price,
                'quantity' => (int) $service->quantity,
                'total_price' => (float) $service->total_price,
                'renewal_date' => $service->renewal_date?->format('M d, Y'),
                'status' => $service->status,
                'is_overdue' => $service->isOverdue(),
                'site' => $service->site ? [
                    'id' => $service->site->id,
                    'name' => $service->site->name,
                ] : null,
            ];
        });

        // Get clients for filter dropdown
        $clients = AgencyClient::select('id', 'client_name', 'company_name')
            ->orderBy('client_name')
            ->get();

        return Inertia::render('GrowBuilder/Billing/Services', [
            'services' => $services,
            'clients' => $clients,
            'filters' => [
                'client_id' => $request->get('client_id'),
                'status' => $request->get('status', 'all'),
                'service_type' => $request->get('service_type', 'all'),
            ],
            'stats' => [
                'total' => AgencyClientService::count(),
                'active' => AgencyClientService::where('status', 'active')->count(),
                'due_for_renewal' => AgencyClientService::dueForRenewal(30)->count(),
                'overdue' => AgencyClientService::overdue()->count(),
            ],
        ]);
    }

    /**
     * Show create service form
     */
    public function create(Request $request): Response
    {
        $clients = AgencyClient::with('sites')
            ->orderBy('client_name')
            ->get()
            ->map(fn($client) => [
                'id' => $client->id,
                'client_name' => $client->client_name,
                'company_name' => $client->company_name,
                'sites' => $client->sites->map(fn($site) => [
                    'id' => $site->id,
                    'name' => $site->name,
                ]),
            ]);

        return Inertia::render('GrowBuilder/Billing/ServiceForm', [
            'clients' => $clients,
            'service' => null,
            'clientId' => $request->get('client_id'),
        ]);
    }

    /**
     * Store new service
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:agency_clients,id',
            'service_type' => 'required|in:website,hosting,domain_management,seo,maintenance,ads,redesign,content_updates,other',
            'service_name' => 'required|string|max:255',
            'linked_site_id' => 'nullable|exists:growbuilder_sites,id',
            'billing_model' => 'required|in:monthly,quarterly,annual,one_time',
            'unit_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'nullable|date',
            'renewal_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:active,paused,cancelled',
            'notes' => 'nullable|string',
        ]);

        $service = AgencyClientService::create($validated);

        // Log activity
        $this->activityLogger->log(
            $service->client->agency_id,
            'service_created',
            'service',
            $service->id,
            "Created service: {$service->service_name} for client: {$service->client->client_name}"
        );

        return redirect()->route('growbuilder.services.index')
            ->with('success', 'Service created successfully.');
    }

    /**
     * Show service details
     */
    public function show(int $id): Response
    {
        $service = AgencyClientService::with(['client', 'site', 'invoiceItems.invoice'])
            ->findOrFail($id);

        return Inertia::render('GrowBuilder/Billing/ServiceDetails', [
            'service' => [
                'id' => $service->id,
                'service_name' => $service->service_name,
                'service_type' => $service->service_type,
                'client' => [
                    'id' => $service->client->id,
                    'name' => $service->client->client_name,
                    'company_name' => $service->client->company_name,
                ],
                'billing_model' => $service->billing_model,
                'unit_price' => (float) $service->unit_price,
                'quantity' => (int) $service->quantity,
                'total_price' => (float) $service->total_price,
                'start_date' => $service->start_date?->format('M d, Y'),
                'renewal_date' => $service->renewal_date?->format('M d, Y'),
                'status' => $service->status,
                'notes' => $service->notes,
                'is_overdue' => $service->isOverdue(),
                'site' => $service->site ? [
                    'id' => $service->site->id,
                    'name' => $service->site->name,
                ] : null,
                'invoices' => $service->invoiceItems->map(fn($item) => [
                    'id' => $item->invoice->id,
                    'invoice_number' => $item->invoice->invoice_number,
                    'invoice_date' => $item->invoice->invoice_date->format('M d, Y'),
                    'total' => (float) $item->invoice->total,
                    'payment_status' => $item->invoice->payment_status,
                ]),
            ],
        ]);
    }

    /**
     * Show edit service form
     */
    public function edit(int $id): Response
    {
        $service = AgencyClientService::with('client.sites')->findOrFail($id);

        $clients = AgencyClient::with('sites')
            ->orderBy('client_name')
            ->get()
            ->map(fn($client) => [
                'id' => $client->id,
                'client_name' => $client->client_name,
                'company_name' => $client->company_name,
                'sites' => $client->sites->map(fn($site) => [
                    'id' => $site->id,
                    'name' => $site->name,
                ]),
            ]);

        return Inertia::render('GrowBuilder/Billing/ServiceForm', [
            'clients' => $clients,
            'service' => [
                'id' => $service->id,
                'client_id' => $service->client_id,
                'service_type' => $service->service_type,
                'service_name' => $service->service_name,
                'linked_site_id' => $service->linked_site_id,
                'billing_model' => $service->billing_model,
                'unit_price' => $service->unit_price,
                'quantity' => $service->quantity,
                'start_date' => $service->start_date?->format('Y-m-d'),
                'renewal_date' => $service->renewal_date?->format('Y-m-d'),
                'status' => $service->status,
                'notes' => $service->notes,
            ],
        ]);
    }

    /**
     * Update service
     */
    public function update(Request $request, int $id)
    {
        $service = AgencyClientService::findOrFail($id);

        $validated = $request->validate([
            'client_id' => 'required|exists:agency_clients,id',
            'service_type' => 'required|in:website,hosting,domain_management,seo,maintenance,ads,redesign,content_updates,other',
            'service_name' => 'required|string|max:255',
            'linked_site_id' => 'nullable|exists:growbuilder_sites,id',
            'billing_model' => 'required|in:monthly,quarterly,annual,one_time',
            'unit_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'nullable|date',
            'renewal_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:active,paused,cancelled',
            'notes' => 'nullable|string',
        ]);

        $service->update($validated);

        // Log activity
        $this->activityLogger->log(
            $service->client->agency_id,
            'service_updated',
            'service',
            $service->id,
            "Updated service: {$service->service_name}"
        );

        return redirect()->route('growbuilder.services.index')
            ->with('success', 'Service updated successfully.');
    }

    /**
     * Delete service
     */
    public function destroy(int $id)
    {
        $service = AgencyClientService::findOrFail($id);
        $serviceName = $service->service_name;
        
        $service->delete();

        return redirect()->route('growbuilder.services.index')
            ->with('success', "Service '{$serviceName}' deleted successfully.");
    }
}
