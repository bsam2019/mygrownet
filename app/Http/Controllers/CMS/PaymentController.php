<?php

namespace App\Http\Controllers\CMS;

use App\Domain\CMS\Core\Services\PaymentService;
use App\Domain\CMS\Core\Services\PdfPaymentReceiptService;
use App\Domain\CMS\Core\Services\CompanySettingsService;
use App\Domain\CMS\Core\ValueObjects\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PaymentModel;
use App\Mail\CMS\PaymentReceivedMail;
use App\Notifications\CMS\PaymentReceivedNotification;
use App\Services\CMS\EmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly PdfPaymentReceiptService $receiptService,
        private readonly EmailService $emailService
    ) {}

    public function index(Request $request): Response
    {
        $cmsUser = $request->user()->cmsUser;
        $companyId = $cmsUser->company_id;

        $query = PaymentModel::where('company_id', $companyId)
            ->with(['customer', 'allocations.invoice']);

        // Filter by customer
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $payments = $query->latest('payment_date')->paginate(20);

        $customers = CustomerModel::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('CMS/Payments/Index', [
            'payments' => $payments,
            'customers' => $customers,
            'filters' => [
                'customer_id' => $request->customer_id ?? null,
                'search' => $request->search ?? '',
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $cmsUser = $request->user()->cmsUser;
        $companyId = $cmsUser->company_id;

        $customers = CustomerModel::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'outstanding_balance']);

        // Get customer invoices if customer_id provided
        $customerInvoices = [];
        if ($request->has('customer_id')) {
            $customerInvoices = InvoiceModel::where('company_id', $companyId)
                ->where('customer_id', $request->customer_id)
                ->whereIn('status', ['sent', 'partial'])
                ->with('items')
                ->get()
                ->map(fn($inv) => [
                    'id' => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'invoice_date' => $inv->invoice_date,
                    'total_amount' => $inv->total_amount,
                    'amount_paid' => $inv->amount_paid,
                    'balance_due' => $inv->total_amount - $inv->amount_paid,
                ]);
        }

        return Inertia::render('CMS/Payments/Create', [
            'customers' => $customers,
            'customerInvoices' => $customerInvoices,
            'paymentMethods' => PaymentMethod::all(),
            'preselectedCustomerId' => $request->customer_id ?? null,
        ]);
    }

    /**
     * JSON endpoint: return unpaid invoices for a customer (used by the Create form)
     */
    public function customerInvoices(Request $request, int $customerId)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $invoices = InvoiceModel::where('company_id', $companyId)
            ->where('customer_id', $customerId)
            ->whereIn('status', ['sent', 'partial'])
            ->orderBy('invoice_date', 'desc')
            ->get()
            ->map(fn($inv) => [
                'id'             => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'invoice_date'   => $inv->invoice_date,
                'total_amount'   => (float) $inv->total_amount,
                'amount_paid'    => (float) $inv->amount_paid,
                'balance_due'    => (float) ($inv->total_amount - $inv->amount_paid),
            ]);

        return response()->json($invoices);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id'      => 'required|exists:cms_customers,id',
            'amount'           => 'required|numeric|min:0.01',
            'payment_method'   => 'required|string',
            'payment_date'     => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:1000',
            'invoice_id'       => 'nullable|exists:cms_invoices,id',
        ]);

        $cmsUser = $request->user()->cmsUser;

        // Build allocations array: ['invoice_id' => amount]
        $allocations = [];
        if (!empty($validated['invoice_id'])) {
            $allocations[$validated['invoice_id']] = $validated['amount'];
        }

        try {
            $payment = $this->paymentService->recordPayment(
                companyId: $cmsUser->company_id,
                customerId: $validated['customer_id'],
                amount: $validated['amount'],
                method: PaymentMethod::from($validated['payment_method']),
                reference: $validated['reference_number'] ?? null,
                notes: $validated['notes'] ?? app(\App\Domain\CMS\Core\Services\CompanySettingsService::class)->getDocumentDefaults($cmsUser->company_id, 'receipt')['notes'] ?: null,
                allocations: $allocations,
                createdBy: $cmsUser->id
            );

            // Load relationships
            $payment->load(['customer', 'company', 'allocations.invoice']);
            
            $customer = $payment->customer;
            
            // Send email if customer has email
            if ($customer && $customer->email) {
                // Generate receipt PDF
                $pdf = $this->receiptService->generateReceipt($payment);
                $pdfPath = storage_path("app/temp/receipt-{$payment->receipt_number}.pdf");
                
                // Ensure temp directory exists
                if (!file_exists(storage_path('app/temp'))) {
                    mkdir(storage_path('app/temp'), 0755, true);
                }
                
                file_put_contents($pdfPath, $pdf->output());
                
                // Send email using EmailService
                $emailSent = $this->emailService->sendEmail(
                    company: $payment->company,
                    to: $customer->email,
                    subject: "Payment Received - Receipt #{$payment->receipt_number}",
                    view: 'emails.cms.payment-received',
                    data: [
                        'payment' => $payment,
                        'company' => $payment->company,
                        'customer' => $customer,
                        'recipient_name' => $customer->name,
                    ],
                    emailType: 'payment',
                    referenceType: 'payment',
                    referenceId: $payment->id,
                    attachmentPath: $pdfPath
                );
                
                // Clean up temp file
                if (file_exists($pdfPath)) {
                    unlink($pdfPath);
                }
            }
            
            // Send in-app notification
            if ($customer && $customer->user) {
                $customer->user->notify(new PaymentReceivedNotification([
                    'id' => $payment->id,
                    'reference_number' => $payment->reference_number,
                    'customer_name' => $customer->name,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                ]));
            }

            return redirect()
                ->route('cms.payments.show', $payment->id)
                ->with('success', 'Payment recorded and confirmation email sent');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    public function show(Request $request, int $id): Response
    {
        $cmsUser = $request->user()->cmsUser;

        $payment = PaymentModel::where('company_id', $cmsUser->company_id)
            ->with(['customer', 'allocations.invoice'])
            ->findOrFail($id);

        return Inertia::render('CMS/Payments/Show', [
            'payment' => $payment,
            'paymentMethods' => PaymentMethod::all(),
        ]);
    }

    public function void(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $cmsUser = $request->user()->cmsUser;

        try {
            $this->paymentService->voidPayment($id, $validated['reason'], $cmsUser->id);

            return back()->with('success', 'Payment voided successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to void payment: ' . $e->getMessage());
        }
    }

    public function allocate(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:cms_invoices,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $cmsUser = $request->user()->cmsUser;

        try {
            $this->paymentService->allocatePayment(
                paymentId: $id,
                invoiceId: $validated['invoice_id'],
                amount: $validated['amount'],
                userId: $cmsUser->id
            );

            return back()->with('success', 'Payment allocated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to allocate payment: ' . $e->getMessage());
        }
    }

    /**
     * Download payment receipt PDF
     */
    public function downloadReceipt(Request $request, int $id)
    {
        $cmsUser = $request->user()->cmsUser;

        $payment = PaymentModel::where('company_id', $cmsUser->company_id)
            ->with(['customer', 'company', 'allocations.invoice'])
            ->findOrFail($id);

        // Check if company has BizDocs module enabled
        if ($payment->company->hasBizDocsModule()) {
            try {
                $adapter = app(\App\Domain\CMS\BizDocs\Contracts\DocumentGeneratorInterface::class);
                $pdfContent = $adapter->generateReceiptPdf($payment);
                
                return response($pdfContent)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'attachment; filename="receipt-' . $payment->receipt_number . '.pdf"');
            } catch (\Exception $e) {
                \Log::error('BizDocs receipt PDF generation failed', [
                    'payment_id' => $id,
                    'error' => $e->getMessage(),
                ]);
                // Fall through to existing PDF service
            }
        }

        // Fallback: Use existing PDF service
        return $this->receiptService->downloadReceipt($id);
    }

    /**
     * Preview payment receipt PDF
     */
    public function previewReceipt(Request $request, int $id)
    {
        $cmsUser = $request->user()->cmsUser;

        $payment = PaymentModel::where('company_id', $cmsUser->company_id)
            ->with(['customer', 'company', 'allocations.invoice'])
            ->findOrFail($id);

        // Check if company has BizDocs module enabled
        if ($payment->company->hasBizDocsModule()) {
            try {
                $adapter = app(\App\Domain\CMS\BizDocs\Contracts\DocumentGeneratorInterface::class);
                $pdfContent = $adapter->generateReceiptPdf($payment);
                
                return response($pdfContent)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="receipt-' . $payment->receipt_number . '.pdf"');
            } catch (\Exception $e) {
                \Log::error('BizDocs receipt PDF preview failed', [
                    'payment_id' => $id,
                    'error' => $e->getMessage(),
                ]);
                // Fall through to existing PDF service
            }
        }

        // Fallback: Use existing PDF service
        return $this->receiptService->streamReceipt($id);
    }

    /**
     * Get daily cash summary
     */
    public function dailySummary(Request $request)
    {
        $cmsUser = $request->user()->cmsUser;
        $date = $request->has('date') ? new \DateTime($request->date) : null;

        $summary = $this->paymentService->getDailyCashSummary($cmsUser->company_id, $date);

        return response()->json($summary);
    }

    /**
     * Get customer credit summary
     */
    public function customerCredit(Request $request, int $customerId)
    {
        $summary = $this->paymentService->getCustomerCreditSummary($customerId);

        return response()->json($summary);
    }

    /**
     * Apply customer credit to invoice
     */
    public function applyCredit(Request $request, int $customerId): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:cms_invoices,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $cmsUser = $request->user()->cmsUser;

        try {
            $this->paymentService->applyCreditToInvoice(
                customerId: $customerId,
                invoiceId: $validated['invoice_id'],
                amount: $validated['amount'],
                userId: $cmsUser->id
            );

            return back()->with('success', 'Credit applied successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to apply credit: ' . $e->getMessage());
        }
    }
}
