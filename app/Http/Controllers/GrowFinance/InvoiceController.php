<?php

namespace App\Http\Controllers\GrowFinance;

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
    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;
        $status = $request->get('status');

        $query = GrowFinanceInvoiceModel::forBusiness($businessId)->with('customer');

        if ($status) {
            $query->where('status', $status);
        }

        $invoices = $query->latest('invoice_date')->paginate(20);

        return Inertia::render('GrowFinance/Invoices/Index', [
            'invoices' => $invoices,
            'currentStatus' => $status,
        ]);
    }

    public function create(Request $request): Response
    {
        $businessId = $request->user()->id;

        $customers = GrowFinanceCustomerModel::forBusiness($businessId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        return Inertia::render('GrowFinance/Invoices/Create', [
            'customers' => $customers,
        ]);
    }

    public function store(Request $request): RedirectResponse
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

        DB::transaction(function () use ($validated, $businessId) {
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

        return redirect()->route('growfinance.invoices.index')
            ->with('success', 'Invoice created successfully!');
    }

    public function show(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $invoice = GrowFinanceInvoiceModel::forBusiness($businessId)
            ->with(['customer', 'items', 'payments'])
            ->findOrFail($id);

        return Inertia::render('GrowFinance/Invoices/Show', [
            'invoice' => $invoice,
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

    private function generateInvoiceNumber(int $businessId): string
    {
        $lastInvoice = GrowFinanceInvoiceModel::forBusiness($businessId)->orderBy('id', 'desc')->first();
        $nextNumber = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, 4)) + 1 : 1;
        return 'INV-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
