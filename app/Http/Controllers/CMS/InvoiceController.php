<?php

namespace App\Http\Controllers\CMS;

use App\Domain\CMS\Core\Services\InvoiceService;
use App\Domain\CMS\Core\Services\PdfInvoiceService;
use App\Domain\CMS\Core\ValueObjects\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Mail\CMS\InvoiceSentMail;
use App\Notifications\CMS\InvoiceSentNotification;
use App\Services\CMS\EmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly PdfInvoiceService $pdfService,
        private readonly EmailService $emailService
    ) {}

    /**
     * Get the company ID for the authenticated CMS user
     */
    private function getCompanyId(Request $request): ?int
    {
        $user = $request->user();
        $cmsUser = \App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel::where('user_id', $user->id)->first();
        
        return $cmsUser?->company_id;
    }

    /**
     * Get CMS user or fail with redirect
     */
    private function getCmsUserOrFail(Request $request)
    {
        $user = $request->user();
        $cmsUser = \App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel::where('user_id', $user->id)->first();
        
        if (!$cmsUser || !$cmsUser->company_id) {
            abort(403, 'You must be associated with a company.');
        }
        
        return $cmsUser;
    }

    public function index(Request $request): Response
    {
        $user = $request->user();
        $cmsUser = \App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel::where('user_id', $user->id)->first();
        
        if (!$cmsUser || !$cmsUser->company_id) {
            return Inertia::render('CMS/Invoices/Index', [
                'invoices' => ['data' => [], 'links' => [], 'meta' => []],
                'summary' => [
                    'total_invoices' => 0,
                    'draft_count' => 0,
                    'sent_count' => 0,
                    'partial_count' => 0,
                    'paid_count' => 0,
                    'total_value' => 0,
                    'total_paid' => 0,
                    'total_outstanding' => 0,
                ],
                'filters' => [
                    'status' => 'all',
                    'search' => '',
                ],
                'statuses' => InvoiceStatus::all(),
            ])->with('error', 'You must be associated with a company to view invoices.');
        }
        
        $companyId = $cmsUser->company_id;

        $query = InvoiceModel::where('company_id', $companyId)
            ->with(['customer', 'items']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->latest('invoice_date')->paginate(20);

        // Get summary stats
        $summary = $this->invoiceService->getInvoiceSummary($companyId);

        return Inertia::render('CMS/Invoices/Index', [
            'invoices' => $invoices,
            'summary' => $summary,
            'filters' => [
                'status' => $request->status ?? 'all',
                'search' => $request->search ?? '',
            ],
            'statuses' => InvoiceStatus::all(),
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();
        $cmsUser = \App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel::where('user_id', $user->id)->first();
        
        if (!$cmsUser || !$cmsUser->company_id) {
            return redirect()->route('cms.invoices.index')->with('error', 'You must be associated with a company.');
        }
        
        $companyId = $cmsUser->company_id;

        $customers = CustomerModel::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'outstanding_balance']);

        return Inertia::render('CMS/Invoices/Create', [
            'customers' => $customers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:cms_customers,id',
            'due_date' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $cmsUser = $this->getCmsUserOrFail($request);

        try {
            $invoice = $this->invoiceService->createInvoice(
                companyId: $cmsUser->company_id,
                customerId: $validated['customer_id'],
                items: $validated['items'],
                dueDate: $validated['due_date'] ?? null,
                notes: $validated['notes'] ?? null,
                createdBy: $cmsUser->id
            );

            return redirect()
                ->route('cms.invoices.show', $invoice->id)
                ->with('success', 'Invoice created successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    public function show(Request $request, int $id): Response
    {
        $cmsUser = $this->getCmsUserOrFail($request);

        $invoice = InvoiceModel::where('company_id', $cmsUser->company_id)
            ->with(['customer', 'items', 'payments.allocations'])
            ->findOrFail($id);

        return Inertia::render('CMS/Invoices/Show', [
            'invoice' => $invoice,
            'statuses' => InvoiceStatus::all(),
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $cmsUser = $this->getCmsUserOrFail($request);
        $companyId = $cmsUser->company_id;

        $invoice = InvoiceModel::where('company_id', $companyId)
            ->with(['customer', 'items'])
            ->findOrFail($id);

        // Only draft invoices can be edited
        if ($invoice->status !== InvoiceStatus::DRAFT->value) {
            return redirect()
                ->route('cms.invoices.show', $id)
                ->with('error', 'Only draft invoices can be edited');
        }

        $customers = CustomerModel::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        return Inertia::render('CMS/Invoices/Edit', [
            'invoice' => $invoice,
            'customers' => $customers,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $cmsUser = $this->getCmsUserOrFail($request);

        try {
            $invoice = $this->invoiceService->updateInvoice(
                invoiceId: $id,
                items: $validated['items'],
                dueDate: $validated['due_date'] ?? null,
                notes: $validated['notes'] ?? null,
                userId: $cmsUser->id
            );

            return redirect()
                ->route('cms.invoices.show', $invoice->id)
                ->with('success', 'Invoice updated successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update invoice: ' . $e->getMessage());
        }
    }

    public function send(Request $request, int $id): RedirectResponse
    {
        $cmsUser = $this->getCmsUserOrFail($request);

        try {
            $invoice = $this->invoiceService->sendInvoice($id, $cmsUser->id);
            
            // Load relationships
            $invoice->load(['customer', 'company', 'items']);
            
            $customer = $invoice->customer;
            
            // Send email if customer has email
            if ($customer && $customer->email) {
                // Generate PDF
                $pdf = $this->pdfService->generate($invoice);
                $pdfPath = storage_path("app/temp/invoice-{$invoice->invoice_number}.pdf");
                
                // Ensure temp directory exists
                if (!file_exists(storage_path('app/temp'))) {
                    mkdir(storage_path('app/temp'), 0755, true);
                }
                
                file_put_contents($pdfPath, $pdf->output());
                
                // Send email using EmailService
                $emailSent = $this->emailService->sendEmail(
                    company: $invoice->company,
                    to: $customer->email,
                    subject: "Invoice {$invoice->invoice_number} from {$invoice->company->name}",
                    view: 'emails.cms.invoice-sent',
                    data: [
                        'invoice' => $invoice,
                        'company' => $invoice->company,
                        'customer' => $customer,
                        'recipient_name' => $customer->name,
                    ],
                    emailType: 'invoice',
                    referenceType: 'invoice',
                    referenceId: $invoice->id,
                    attachmentPath: $pdfPath
                );
                
                // Clean up temp file
                if (file_exists($pdfPath)) {
                    unlink($pdfPath);
                }
                
                if ($emailSent) {
                    // Send in-app notification
                    if ($customer->user) {
                        $customer->user->notify(new InvoiceSentNotification([
                            'id' => $invoice->id,
                            'invoice_number' => $invoice->invoice_number,
                            'customer_name' => $customer->name,
                            'total_amount' => $invoice->total_amount,
                            'due_date' => $invoice->due_date?->format('Y-m-d'),
                        ]));
                    }
                    
                    return back()->with('success', 'Invoice sent successfully via email');
                } else {
                    return back()->with('warning', 'Invoice marked as sent but email delivery failed');
                }
            }

            return back()->with('success', 'Invoice marked as sent');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send invoice: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $cmsUser = $this->getCmsUserOrFail($request);

        try {
            $this->invoiceService->cancelInvoice($id, $validated['reason'], $cmsUser->id);

            return back()->with('success', 'Invoice cancelled');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel invoice: ' . $e->getMessage());
        }
    }

    public function void(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $cmsUser = $this->getCmsUserOrFail($request);

        try {
            $this->invoiceService->voidInvoice($id, $validated['reason'], $cmsUser->id);

            return back()->with('success', 'Invoice voided');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to void invoice: ' . $e->getMessage());
        }
    }

    public function downloadPdf(Request $request, int $id)
    {
        $cmsUser = $this->getCmsUserOrFail($request);

        $invoice = InvoiceModel::where('company_id', $cmsUser->company_id)
            ->with(['customer', 'items', 'company'])
            ->findOrFail($id);

        return $this->pdfService->download($invoice);
    }

    public function previewPdf(Request $request, int $id)
    {
        $cmsUser = $this->getCmsUserOrFail($request);

        $invoice = InvoiceModel::where('company_id', $cmsUser->company_id)
            ->with(['customer', 'items', 'company'])
            ->findOrFail($id);

        return $this->pdfService->stream($invoice);
    }
}
