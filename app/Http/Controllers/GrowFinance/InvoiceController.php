<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\PdfInvoiceService;
use App\Domain\Module\Services\SubscriptionService;
use App\Domain\GrowFinance\ValueObjects\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceCustomerModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceItemModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private PdfInvoiceService $pdfService
    ) {}
    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;
        $status = $request->get('status');

        $query = GrowFinanceInvoiceModel::forBusiness($businessId)->with('customer');

        if ($status) {
            $query->where('status', $status);
        }

        $invoices = $query->latest('invoice_date')->paginate(20);

        // Get invoice usage for limit banner
        $invoiceUsage = $this->subscriptionService->canCreateInvoice($request->user());

        return Inertia::render('GrowFinance/Invoices/Index', [
            'invoices' => $invoices,
            'currentStatus' => $status,
            'invoiceUsage' => $invoiceUsage,
        ]);
    }

    public function create(Request $request): Response
    {
        $businessId = $request->user()->id;

        $customers = GrowFinanceCustomerModel::forBusiness($businessId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        // Get available templates for the user
        $templates = \App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceTemplateModel::forBusiness($businessId)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'is_default', 'colors']);

        $defaultTemplate = $templates->firstWhere('is_default', true);

        return Inertia::render('GrowFinance/Invoices/Create', [
            'customers' => $customers,
            'templates' => $templates,
            'defaultTemplateId' => $defaultTemplate?->id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Check subscription limits
        $check = $this->subscriptionService->canCreateInvoice($request->user());
        if (!$check['allowed']) {
            return back()->with('error', 'You\'ve reached your monthly invoice limit. Please upgrade your plan to create more invoices.');
        }

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:growfinance_customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string|max:500',
            'template_id' => 'nullable|exists:growfinance_invoice_templates,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $businessId = $request->user()->id;

        // Get template_id - use provided, or default, or null
        $templateId = $validated['template_id'] ?? null;
        if (!$templateId) {
            $defaultTemplate = \App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceTemplateModel::forBusiness($businessId)
                ->where('is_default', true)
                ->first();
            $templateId = $defaultTemplate?->id;
        }

        DB::transaction(function () use ($validated, $businessId, $templateId) {
            $invoiceNumber = $this->generateInvoiceNumber($businessId);
            $subtotal = collect($validated['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);

            $invoice = GrowFinanceInvoiceModel::create([
                'business_id' => $businessId,
                'customer_id' => $validated['customer_id'],
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'status' => InvoiceStatus::DRAFT,
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'notes' => $validated['notes'],
                'template_id' => $templateId,
            ]);

            foreach ($validated['items'] as $item) {
                GrowFinanceInvoiceItemModel::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }
        });

        // Clear usage cache after creating invoice
        $this->subscriptionService->clearUsageCache($request->user());

        return redirect()->route('growfinance.invoices.index')
            ->with('success', 'Invoice created successfully!');
    }

    public function show(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $invoice = GrowFinanceInvoiceModel::forBusiness($businessId)
            ->with(['customer', 'items', 'payments'])
            ->findOrFail($id);

        // Get available templates
        $templates = \App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceTemplateModel::forBusiness($businessId)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'is_default', 'colors']);

        // Get current template
        $currentTemplate = null;
        if ($invoice->template_id) {
            $currentTemplate = $templates->firstWhere('id', $invoice->template_id);
        }
        if (!$currentTemplate) {
            $currentTemplate = $templates->firstWhere('is_default', true);
        }

        return Inertia::render('GrowFinance/Invoices/Show', [
            'invoice' => $invoice,
            'templates' => $templates,
            'currentTemplate' => $currentTemplate,
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $invoice = GrowFinanceInvoiceModel::forBusiness($businessId)
            ->with('items')
            ->findOrFail($id);

        $customers = GrowFinanceCustomerModel::forBusiness($businessId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('GrowFinance/Invoices/Edit', [
            'invoice' => $invoice,
            'customers' => $customers,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:growfinance_customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $businessId = $request->user()->id;

        DB::transaction(function () use ($validated, $businessId, $id) {
            $invoice = GrowFinanceInvoiceModel::forBusiness($businessId)->findOrFail($id);
            
            $subtotal = collect($validated['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);

            $invoice->update([
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'notes' => $validated['notes'],
            ]);

            // Delete existing items and recreate
            $invoice->items()->delete();

            foreach ($validated['items'] as $item) {
                GrowFinanceInvoiceItemModel::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }
        });

        return redirect()->route('growfinance.invoices.index')
            ->with('success', 'Invoice updated successfully!');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;

        $invoice = GrowFinanceInvoiceModel::forBusiness($businessId)->findOrFail($id);
        
        // Only allow deleting draft invoices
        if ($invoice->status !== InvoiceStatus::DRAFT->value) {
            return back()->withErrors(['error' => 'Only draft invoices can be deleted.']);
        }

        $invoice->items()->delete();
        $invoice->delete();

        return back()->with('success', 'Invoice deleted successfully!');
    }

    public function recordPayment(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank,mobile_money,cheque',
            'reference' => 'nullable|string|max:100',
        ]);

        $businessId = $request->user()->id;
        $invoice = GrowFinanceInvoiceModel::forBusiness($businessId)->findOrFail($id);

        $invoice->amount_paid += $validated['amount'];
        $invoice->status = $invoice->amount_paid >= $invoice->total_amount
            ? InvoiceStatus::PAID
            : InvoiceStatus::PARTIAL;
        $invoice->save();

        return back()->with('success', 'Payment recorded successfully!');
    }

    public function send(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;
        $invoice = GrowFinanceInvoiceModel::forBusiness($businessId)->findOrFail($id);

        // Update status to sent
        $invoice->status = InvoiceStatus::SENT;
        $invoice->save();

        // TODO: Send email notification to customer

        return back()->with('success', 'Invoice sent successfully!');
    }

    /**
     * Download invoice as PDF (Professional+ feature)
     */
    public function downloadPdf(Request $request, int $id)
    {
        // Check if user has PDF export feature
        if (!$this->subscriptionService->canPerformAction($request->user(), 'pdf_export')) {
            return back()->with('error', 'PDF export is available on Professional plan and above. Please upgrade to access this feature.');
        }

        $businessId = $request->user()->id;
        $invoice = GrowFinanceInvoiceModel::forBusiness($businessId)
            ->with(['customer', 'items'])
            ->findOrFail($id);

        return $this->pdfService->download($invoice, $request->user());
    }

    /**
     * Preview invoice PDF in browser
     */
    public function previewPdf(Request $request, int $id)
    {
        // Check if user has PDF export feature
        if (!$this->subscriptionService->canPerformAction($request->user(), 'pdf_export')) {
            return back()->with('error', 'PDF preview is available on Professional plan and above.');
        }

        $businessId = $request->user()->id;
        $invoice = GrowFinanceInvoiceModel::forBusiness($businessId)
            ->with(['customer', 'items'])
            ->findOrFail($id);

        $pdfContent = $this->pdfService->generate($invoice, $request->user());

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Invoice-' . $invoice->invoice_number . '.pdf"',
        ]);
    }

    /**
     * Print view for invoice (HTML version for browser printing)
     */
    public function printView(Request $request, int $id)
    {
        $businessId = $request->user()->id;
        $invoice = GrowFinanceInvoiceModel::forBusiness($businessId)
            ->with(['customer', 'items'])
            ->findOrFail($id);

        // Get the template
        $template = null;
        if ($invoice->template_id) {
            $template = \App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceTemplateModel::find($invoice->template_id);
        }
        if (!$template) {
            $template = \App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceTemplateModel::forBusiness($businessId)
                ->where('is_default', true)
                ->first();
        }

        // Get business profile
        $profile = \App\Infrastructure\Persistence\Eloquent\GrowFinanceProfileModel::where('user_id', $businessId)->first();

        return view('growfinance.invoices.print', [
            'invoice' => $invoice,
            'template' => $template,
            'profile' => $profile,
        ]);
    }

    /**
     * Update invoice template
     */
    public function updateTemplate(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:growfinance_invoice_templates,id',
        ]);

        $businessId = $request->user()->id;
        $invoice = GrowFinanceInvoiceModel::forBusiness($businessId)->findOrFail($id);

        $invoice->update(['template_id' => $validated['template_id']]);

        return back()->with('success', 'Invoice template updated!');
    }

    /**
     * Send invoice via email
     */
    public function sendEmail(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;
        $invoice = GrowFinanceInvoiceModel::forBusiness($businessId)
            ->with('customer')
            ->findOrFail($id);

        if (!$invoice->customer || !$invoice->customer->email) {
            return back()->with('error', 'Customer email address is required to send invoice.');
        }

        // Update status to sent
        if ($invoice->status === InvoiceStatus::DRAFT->value) {
            $invoice->status = InvoiceStatus::SENT;
            $invoice->save();
        }

        // TODO: Implement actual email sending
        // Mail::to($invoice->customer->email)->send(new InvoiceMail($invoice));

        return back()->with('success', 'Invoice sent to ' . $invoice->customer->email);
    }

    private function generateInvoiceNumber(int $businessId): string
    {
        $lastInvoice = GrowFinanceInvoiceModel::forBusiness($businessId)->orderBy('id', 'desc')->first();
        $nextNumber = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, 4)) + 1 : 1;
        return 'INV-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
