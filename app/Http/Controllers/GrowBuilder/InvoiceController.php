<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Models\AgencyClient;
use App\Models\AgencyClientInvoice;
use App\Models\AgencyClientInvoiceItem;
use App\Models\AgencyClientPayment;
use App\Models\AgencyClientService;
use App\Services\GrowBuilder\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function __construct(
        private ActivityLogger $activityLogger
    ) {}

    /**
     * Display list of invoices
     */
    public function index(Request $request): Response
    {
        $query = AgencyClientInvoice::with(['client', 'items', 'payments'])
            ->orderBy('invoice_date', 'desc');

        // Filter by client
        if ($request->has('client_id') && $request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        // Search by invoice number
        if ($request->has('search') && $request->search) {
            $query->where('invoice_number', 'like', "%{$request->search}%");
        }

        $invoices = $query->paginate(20)->through(function ($invoice) {
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client' => [
                    'id' => $invoice->client->id,
                    'name' => $invoice->client->client_name,
                    'company_name' => $invoice->client->company_name,
                ],
                'invoice_date' => $invoice->invoice_date->format('M d, Y'),
                'due_date' => $invoice->due_date->format('M d, Y'),
                'total' => $invoice->total,
                'total_paid' => $invoice->total_paid,
                'balance' => $invoice->balance,
                'payment_status' => $invoice->payment_status,
                'is_overdue' => $invoice->isOverdue(),
                'currency' => $invoice->currency,
            ];
        });

        // Get clients for filter dropdown
        $clients = AgencyClient::select('id', 'client_name', 'company_name')
            ->orderBy('client_name')
            ->get();

        return Inertia::render('GrowBuilder/Billing/Invoices', [
            'invoices' => $invoices,
            'clients' => $clients,
            'filters' => [
                'client_id' => $request->get('client_id'),
                'payment_status' => $request->get('payment_status', 'all'),
                'search' => $request->get('search'),
            ],
            'stats' => [
                'total' => AgencyClientInvoice::count(),
                'unpaid' => AgencyClientInvoice::unpaid()->count(),
                'overdue' => AgencyClientInvoice::overdue()->count(),
                'total_outstanding' => AgencyClientInvoice::unpaid()->sum('total'),
            ],
        ]);
    }

    /**
     * Show create invoice form
     */
    public function create(Request $request): Response
    {
        $clients = AgencyClient::with('services')
            ->orderBy('client_name')
            ->get()
            ->map(fn($client) => [
                'id' => $client->id,
                'client_name' => $client->client_name,
                'company_name' => $client->company_name,
                'services' => $client->services->where('status', 'active')->map(fn($service) => [
                    'id' => $service->id,
                    'service_name' => $service->service_name,
                    'unit_price' => $service->unit_price,
                    'quantity' => $service->quantity,
                ]),
            ]);

        return Inertia::render('GrowBuilder/Billing/InvoiceForm', [
            'clients' => $clients,
            'invoice' => null,
            'clientId' => $request->get('client_id'),
        ]);
    }

    /**
     * Store new invoice
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:agency_clients,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'currency' => 'nullable|string|max:3',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:agency_client_services,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        // Calculate totals
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += $item['quantity'] * $item['amount'];
        }

        // Create invoice
        $invoice = AgencyClientInvoice::create([
            'client_id' => $validated['client_id'],
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'subtotal' => $subtotal,
            'tax' => 0, // Can be calculated if needed
            'total' => $subtotal,
            'currency' => $validated['currency'] ?? 'ZMW',
            'payment_status' => 'draft',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Create invoice items
        foreach ($validated['items'] as $item) {
            AgencyClientInvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $item['service_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'amount' => $item['amount'],
                'total' => $item['quantity'] * $item['amount'],
            ]);
        }

        // Log activity
        $this->activityLogger->log(
            'invoice_created',
            'invoice',
            $invoice->id,
            "Created invoice {$invoice->invoice_number} for client: {$invoice->client->client_name}"
        );

        return redirect()->route('growbuilder.invoices.show', $invoice->id)
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Show invoice details
     */
    public function show(int $id): Response
    {
        $invoice = AgencyClientInvoice::with(['client', 'items.service', 'payments'])
            ->findOrFail($id);

        return Inertia::render('GrowBuilder/Billing/InvoiceDetails', [
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client' => [
                    'id' => $invoice->client->id,
                    'name' => $invoice->client->client_name,
                    'company_name' => $invoice->client->company_name,
                    'email' => $invoice->client->email,
                    'phone' => $invoice->client->phone,
                    'address' => $invoice->client->address,
                    'city' => $invoice->client->city,
                    'country' => $invoice->client->country,
                ],
                'invoice_date' => $invoice->invoice_date->format('M d, Y'),
                'due_date' => $invoice->due_date->format('M d, Y'),
                'subtotal' => $invoice->subtotal,
                'tax' => $invoice->tax,
                'total' => $invoice->total,
                'total_paid' => $invoice->total_paid,
                'balance' => $invoice->balance,
                'currency' => $invoice->currency,
                'payment_status' => $invoice->payment_status,
                'is_overdue' => $invoice->isOverdue(),
                'notes' => $invoice->notes,
                'items' => $invoice->items->map(fn($item) => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'amount' => $item->amount,
                    'total' => $item->total,
                    'service' => $item->service ? [
                        'id' => $item->service->id,
                        'name' => $item->service->service_name,
                    ] : null,
                ]),
                'payments' => $invoice->payments->map(fn($payment) => [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date->format('M d, Y'),
                    'payment_method' => $payment->payment_method,
                    'reference' => $payment->reference,
                    'notes' => $payment->notes,
                ]),
            ],
        ]);
    }

    /**
     * Show edit invoice form
     */
    public function edit(int $id): Response
    {
        $invoice = AgencyClientInvoice::with(['client.services', 'items'])
            ->findOrFail($id);

        // Can only edit draft invoices
        if ($invoice->payment_status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be edited.');
        }

        $clients = AgencyClient::with('services')
            ->orderBy('client_name')
            ->get()
            ->map(fn($client) => [
                'id' => $client->id,
                'client_name' => $client->client_name,
                'company_name' => $client->company_name,
                'services' => $client->services->where('status', 'active')->map(fn($service) => [
                    'id' => $service->id,
                    'service_name' => $service->service_name,
                    'unit_price' => $service->unit_price,
                    'quantity' => $service->quantity,
                ]),
            ]);

        return Inertia::render('GrowBuilder/Billing/InvoiceForm', [
            'clients' => $clients,
            'invoice' => [
                'id' => $invoice->id,
                'client_id' => $invoice->client_id,
                'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'currency' => $invoice->currency,
                'notes' => $invoice->notes,
                'items' => $invoice->items->map(fn($item) => [
                    'id' => $item->id,
                    'service_id' => $item->service_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'amount' => $item->amount,
                ]),
            ],
        ]);
    }

    /**
     * Update invoice
     */
    public function update(Request $request, int $id)
    {
        $invoice = AgencyClientInvoice::findOrFail($id);

        // Can only edit draft invoices
        if ($invoice->payment_status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be edited.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:agency_clients,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'currency' => 'nullable|string|max:3',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:agency_client_services,id',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        // Calculate totals
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += $item['quantity'] * $item['amount'];
        }

        // Update invoice
        $invoice->update([
            'client_id' => $validated['client_id'],
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'currency' => $validated['currency'] ?? 'ZMW',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Delete old items and create new ones
        $invoice->items()->delete();
        foreach ($validated['items'] as $item) {
            AgencyClientInvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $item['service_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'amount' => $item['amount'],
                'total' => $item['quantity'] * $item['amount'],
            ]);
        }

        // Log activity
        $this->activityLogger->log(
            'invoice_updated',
            'invoice',
            $invoice->id,
            "Updated invoice {$invoice->invoice_number}"
        );

        return redirect()->route('growbuilder.invoices.show', $invoice->id)
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Mark invoice as sent
     */
    public function markAsSent(int $id)
    {
        $invoice = AgencyClientInvoice::findOrFail($id);
        $invoice->markAsSent();

        $this->activityLogger->log(
            'invoice_sent',
            'invoice',
            $invoice->id,
            "Marked invoice {$invoice->invoice_number} as sent"
        );

        return back()->with('success', 'Invoice marked as sent.');
    }

    /**
     * Record payment for invoice
     */
    public function recordPayment(Request $request, int $id)
    {
        $invoice = AgencyClientInvoice::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->balance,
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string|max:100',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $payment = AgencyClientPayment::create([
            'invoice_id' => $invoice->id,
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'] ?? null,
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->activityLogger->log(
            'payment_recorded',
            'payment',
            $payment->id,
            "Recorded payment of {$invoice->currency} {$validated['amount']} for invoice {$invoice->invoice_number}"
        );

        return back()->with('success', 'Payment recorded successfully.');
    }

    /**
     * Delete invoice
     */
    public function destroy(int $id)
    {
        $invoice = AgencyClientInvoice::findOrFail($id);

        // Can only delete draft invoices
        if ($invoice->payment_status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be deleted.');
        }

        $invoiceNumber = $invoice->invoice_number;
        $invoice->delete();

        return redirect()->route('growbuilder.invoices.index')
            ->with('success', "Invoice {$invoiceNumber} deleted successfully.");
    }

    /**
     * Generate invoice from services
     */
    public function generateFromServices(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:agency_clients,id',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:agency_client_services,id',
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date',
        ]);

        $client = AgencyClient::findOrFail($validated['client_id']);
        $services = AgencyClientService::whereIn('id', $validated['service_ids'])->get();

        // Calculate totals
        $subtotal = $services->sum('total_price');

        // Create invoice
        $invoice = AgencyClientInvoice::create([
            'client_id' => $client->id,
            'invoice_date' => $validated['invoice_date'] ?? Carbon::now(),
            'due_date' => $validated['due_date'] ?? Carbon::now()->addDays(30),
            'subtotal' => $subtotal,
            'tax' => 0,
            'total' => $subtotal,
            'currency' => 'ZMW',
            'payment_status' => 'draft',
        ]);

        // Create invoice items from services
        foreach ($services as $service) {
            AgencyClientInvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $service->id,
                'description' => $service->service_name,
                'quantity' => $service->quantity,
                'amount' => $service->unit_price,
                'total' => $service->total_price,
            ]);
        }

        $this->activityLogger->log(
            'invoice_generated',
            'invoice',
            $invoice->id,
            "Generated invoice {$invoice->invoice_number} from services for client: {$client->client_name}"
        );

        return redirect()->route('growbuilder.invoices.show', $invoice->id)
            ->with('success', 'Invoice generated successfully from services.');
    }
}
