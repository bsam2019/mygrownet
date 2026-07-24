<?php

namespace App\Http\Controllers\BMS;

use App\Domain\BMS\Core\Services\PaymentService;
use App\Domain\BMS\Core\Services\PdfPaymentReceiptService;
use App\Domain\BMS\Core\Services\CompanySettingsService;
use App\Domain\BMS\Core\ValueObjects\PaymentMethod;
use App\Domain\BMS\Repositories\PaymentRepositoryInterface;
use App\Domain\BMS\Repositories\CustomerRepositoryInterface;
use App\Domain\BMS\Repositories\InvoiceRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BMS\PaymentModel;
use App\Infrastructure\Persistence\Eloquent\BMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\BMS\CustomerModel;
use App\Notifications\BMS\PaymentReceivedNotification;
use App\Services\BMS\EmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly PdfPaymentReceiptService $receiptService,
        private readonly EmailService $emailService,
        private readonly PaymentRepositoryInterface $paymentRepo,
        private readonly CustomerRepositoryInterface $customerRepo,
        private readonly InvoiceRepositoryInterface $invoiceRepo
    ) {}

    public function index(Request $request): Response
    {
        $cmsUser = $request->user()->cmsUser;
        $companyId = $cmsUser->company_id;

        $query = PaymentModel::where('company_id', $companyId)
            ->with(['customer', 'allocations.invoice']);

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

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

        return Inertia::render('BMS/Payments/Index', [
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

        $customerInvoices = [];
        if ($request->has('customer_id')) {
            $invoices = InvoiceModel::where('company_id', $companyId)
                ->where('customer_id', $request->customer_id)
                ->whereIn('status', ['sent', 'partial'])
                ->with('items')
                ->get();
            $customerInvoices = $invoices->map(fn($inv) => [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'invoice_date' => $inv->invoice_date,
                'total_amount' => $inv->total_amount,
                'amount_paid' => $inv->amount_paid,
                'balance_due' => $inv->total_amount - $inv->amount_paid,
            ])->toArray();
        }

        return Inertia::render('BMS/Payments/Create', [
            'customers' => $customers,
            'customerInvoices' => $customerInvoices,
            'paymentMethods' => PaymentMethod::all(),
            'preselectedCustomerId' => $request->customer_id ?? null,
        ]);
    }

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

        $allocations = [];
        if (!empty($validated['invoice_id'])) {
            $allocations[$validated['invoice_id']] = $validated['amount'];
        }

        try {
            $result = $this->paymentService->recordPayment(
                companyId: $cmsUser->company_id,
                customerId: $validated['customer_id'],
                amount: $validated['amount'],
                method: PaymentMethod::from($validated['payment_method']),
                reference: $validated['reference_number'] ?? null,
                notes: $validated['notes'] ?? app(CompanySettingsService::class)->getDocumentDefaults($cmsUser->company_id, 'receipt')['notes'] ?: null,
                allocations: $allocations,
                createdBy: $cmsUser->id
            );

            $paymentEloquent = PaymentModel::with(['customer', 'company', 'allocations.invoice'])
                ->find($result['payment']->id);

            if ($paymentEloquent && $paymentEloquent->customer && $paymentEloquent->customer->email) {
                $pdf = $this->receiptService->generateReceipt($paymentEloquent);
                $pdfPath = storage_path("app/temp/receipt-{$paymentEloquent->receipt_number}.pdf");

                if (!file_exists(storage_path('app/temp'))) {
                    mkdir(storage_path('app/temp'), 0755, true);
                }

                file_put_contents($pdfPath, $pdf->output());

                $emailSent = $this->emailService->sendEmail(
                    company: $paymentEloquent->company,
                    to: $paymentEloquent->customer->email,
                    subject: "Payment Received - Receipt #{$paymentEloquent->receipt_number}",
                    view: 'emails.cms.payment-received',
                    data: [
                        'payment' => $paymentEloquent,
                        'company' => $paymentEloquent->company,
                        'customer' => $paymentEloquent->customer,
                        'recipient_name' => $paymentEloquent->customer->name,
                    ],
                    emailType: 'payment',
                    referenceType: 'payment',
                    referenceId: $paymentEloquent->id,
                    attachmentPath: $pdfPath
                );

                if (file_exists($pdfPath)) {
                    unlink($pdfPath);
                }
            }

            if ($paymentEloquent && $paymentEloquent->customer && $paymentEloquent->customer->user) {
                $paymentEloquent->customer->user->notify(new PaymentReceivedNotification([
                    'id' => $paymentEloquent->id,
                    'reference_number' => $paymentEloquent->reference_number,
                    'customer_name' => $paymentEloquent->customer->name,
                    'amount' => $paymentEloquent->amount,
                    'payment_method' => $paymentEloquent->payment_method,
                ]));
            }

            return redirect()
                ->route('bms.payments.show', $result['payment']->id)
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

        return Inertia::render('BMS/Payments/Show', [
            'payment' => $payment,
            'paymentMethods' => PaymentMethod::all(),
        ]);
    }

    public function void(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate(['reason' => 'required|string|max:500']);
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
            $this->paymentService->allocatePayment($id, $validated['invoice_id'], $validated['amount'], $cmsUser->id);
            return back()->with('success', 'Payment allocated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to allocate payment: ' . $e->getMessage());
        }
    }

    public function downloadReceipt(Request $request, int $id)
    {
        $cmsUser = $request->user()->cmsUser;

        $payment = PaymentModel::where('company_id', $cmsUser->company_id)
            ->with(['customer', 'company', 'allocations.invoice'])
            ->findOrFail($id);

        if ($payment->company->hasBizDocsModule()) {
            try {
                $adapter = app(\App\Domain\BMS\BizDocs\Contracts\DocumentGeneratorInterface::class);
                $pdfContent = $adapter->generateReceiptPdf($payment);
                return response($pdfContent)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'attachment; filename="receipt-' . $payment->receipt_number . '.pdf"');
            } catch (\Exception $e) {
                \Log::error('BizDocs receipt PDF generation failed', ['payment_id' => $id, 'error' => $e->getMessage()]);
            }
        }

        return $this->receiptService->downloadReceipt($id);
    }

    public function previewReceipt(Request $request, int $id)
    {
        $cmsUser = $request->user()->cmsUser;

        $payment = PaymentModel::where('company_id', $cmsUser->company_id)
            ->with(['customer', 'company', 'allocations.invoice'])
            ->findOrFail($id);

        if ($payment->company->hasBizDocsModule()) {
            try {
                $adapter = app(\App\Domain\BMS\BizDocs\Contracts\DocumentGeneratorInterface::class);
                $pdfContent = $adapter->generateReceiptPdf($payment);
                return response($pdfContent)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="receipt-' . $payment->receipt_number . '.pdf"');
            } catch (\Exception $e) {
                \Log::error('BizDocs receipt PDF preview failed', ['payment_id' => $id, 'error' => $e->getMessage()]);
            }
        }

        return $this->receiptService->streamReceipt($id);
    }

    public function dailySummary(Request $request)
    {
        $cmsUser = $request->user()->cmsUser;
        $date = $request->has('date') ? new \DateTime($request->date) : null;
        return response()->json($this->paymentService->getDailyCashSummary($cmsUser->company_id, $date));
    }

    public function customerCredit(Request $request, int $customerId)
    {
        return response()->json($this->paymentService->getCustomerCreditSummary($customerId));
    }

    public function applyCredit(Request $request, int $customerId): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:cms_invoices,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $cmsUser = $request->user()->cmsUser;

        try {
            $this->paymentService->applyCreditToInvoice($customerId, $validated['invoice_id'], $validated['amount'], $cmsUser->id);
            return back()->with('success', 'Credit applied successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to apply credit: ' . $e->getMessage());
        }
    }
}
