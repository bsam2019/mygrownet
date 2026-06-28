<?php

namespace App\Http\Controllers\GrowFinance;

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

class SalesController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;

        $sales = GrowFinanceInvoiceModel::forBusiness($businessId)
            ->with('customer')
            ->latest('invoice_date')
            ->paginate(20);

        $customers = GrowFinanceCustomerModel::forBusiness($businessId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        // Get transaction usage for limit banner
        $transactionUsage = $this->subscriptionService->canCreateTransaction($request->user());

        return Inertia::render('GrowFinance/Sales/Index', [
            'sales' => $sales,
            'customers' => $customers,
            'transactionUsage' => $transactionUsage,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Check subscription limits
        $check = $this->subscriptionService->canCreateTransaction($request->user());
        if (!$check['allowed']) {
            return back()->with('error', 'You\'ve reached your monthly transaction limit. Please upgrade your plan to record more sales.');
        }

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:growfinance_customers,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank,mobile_money',
        ]);

        $businessId = $request->user()->id;

        DB::transaction(function () use ($validated, $businessId) {
            $invoiceNumber = $this->generateInvoiceNumber($businessId);

            $invoice = GrowFinanceInvoiceModel::create([
                'business_id' => $businessId,
                'customer_id' => $validated['customer_id'],
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now(),
                'status' => InvoiceStatus::PAID,
                'subtotal' => $validated['amount'],
                'total_amount' => $validated['amount'],
                'amount_paid' => $validated['amount'],
            ]);

            GrowFinanceInvoiceItemModel::create([
                'invoice_id' => $invoice->id,
                'description' => $validated['description'],
                'quantity' => 1,
                'unit_price' => $validated['amount'],
                'line_total' => $validated['amount'],
            ]);
        });

        // Clear usage cache
        $this->subscriptionService->clearUsageCache($request->user());

        return back()->with('success', 'Sale recorded successfully!');
    }

    private function generateInvoiceNumber(int $businessId): string
    {
        $lastInvoice = GrowFinanceInvoiceModel::forBusiness($businessId)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastInvoice
            ? ((int) substr($lastInvoice->invoice_number, 4)) + 1
            : 1;

        return 'INV-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
