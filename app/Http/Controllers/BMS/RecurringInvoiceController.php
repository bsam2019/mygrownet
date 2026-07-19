<?php

namespace App\Http\Controllers\CMS;

use App\Domain\CMS\Core\Services\RecurringInvoiceService;
use App\Domain\CMS\Core\Services\CustomerService;
use App\Domain\CMS\Core\Services\JobService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecurringInvoiceController extends Controller
{
    public function __construct(
        private RecurringInvoiceService $recurringInvoiceService,
        private CustomerService $customerService,
        private JobService $jobService
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;
        $status = $request->query('status');

        $recurringInvoices = $this->recurringInvoiceService->getAll($companyId, $status);

        return Inertia::render('CMS/RecurringInvoices/Index', [
            'recurringInvoices' => $recurringInvoices,
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    public function create(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $customers = $this->customerService->getAllForCompany($companyId);
        $jobs = $this->jobService->getAllForCompany($companyId);

        return Inertia::render('CMS/RecurringInvoices/Create', [
            'customers' => $customers,
            'jobs' => $jobs,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:cms_customers,id',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.amount' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'interval' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'max_occurrences' => 'nullable|integer|min:1',
            'auto_send_email' => 'boolean',
            'email_to' => 'nullable|email',
            'email_cc' => 'nullable|email',
            'payment_terms_days' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $recurringInvoice = $this->recurringInvoiceService->create($companyId, $validated);

        return redirect()->route('cms.recurring-invoices.show', $recurringInvoice->id)
            ->with('success', 'Recurring invoice created successfully');
    }


    public function show(Request $request, int $id)
    {
        $companyId = $request->user()->cmsUser->company_id;
        $recurringInvoice = $this->recurringInvoiceService->getById($id, $companyId);

        if (!$recurringInvoice) {
            abort(404);
        }

        $history = $this->recurringInvoiceService->getHistory($recurringInvoice);

        return Inertia::render('CMS/RecurringInvoices/Show', [
            'recurringInvoice' => $recurringInvoice,
            'history' => $history,
        ]);
    }

    public function edit(Request $request, int $id)
    {
        $companyId = $request->user()->cmsUser->company_id;
        $recurringInvoice = $this->recurringInvoiceService->getById($id, $companyId);

        if (!$recurringInvoice) {
            abort(404);
        }

        $customers = $this->customerService->getAllForCompany($companyId);
        $jobs = $this->jobService->getAllForCompany($companyId);

        return Inertia::render('CMS/RecurringInvoices/Edit', [
            'recurringInvoice' => $recurringInvoice,
            'customers' => $customers,
            'jobs' => $jobs,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:cms_customers,id',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.amount' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'interval' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'max_occurrences' => 'nullable|integer|min:1',
            'auto_send_email' => 'boolean',
            'email_to' => 'nullable|email',
            'email_cc' => 'nullable|email',
            'payment_terms_days' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $recurringInvoice = $this->recurringInvoiceService->getById($id, $companyId);

        if (!$recurringInvoice) {
            abort(404);
        }

        $this->recurringInvoiceService->update($recurringInvoice, $validated);

        return redirect()->route('cms.recurring-invoices.show', $id)
            ->with('success', 'Recurring invoice updated successfully');
    }

    public function destroy(Request $request, int $id)
    {
        $companyId = $request->user()->cmsUser->company_id;
        $recurringInvoice = $this->recurringInvoiceService->getById($id, $companyId);

        if (!$recurringInvoice) {
            abort(404);
        }

        $this->recurringInvoiceService->delete($recurringInvoice);

        return redirect()->route('cms.recurring-invoices.index')
            ->with('success', 'Recurring invoice deleted successfully');
    }

    public function pause(Request $request, int $id)
    {
        $companyId = $request->user()->cmsUser->company_id;
        $recurringInvoice = $this->recurringInvoiceService->getById($id, $companyId);

        if (!$recurringInvoice) {
            abort(404);
        }

        $this->recurringInvoiceService->pause($recurringInvoice);

        return back()->with('success', 'Recurring invoice paused');
    }

    public function resume(Request $request, int $id)
    {
        $companyId = $request->user()->cmsUser->company_id;
        $recurringInvoice = $this->recurringInvoiceService->getById($id, $companyId);

        if (!$recurringInvoice) {
            abort(404);
        }

        $this->recurringInvoiceService->resume($recurringInvoice);

        return back()->with('success', 'Recurring invoice resumed');
    }

    public function cancel(Request $request, int $id)
    {
        $companyId = $request->user()->cmsUser->company_id;
        $recurringInvoice = $this->recurringInvoiceService->getById($id, $companyId);

        if (!$recurringInvoice) {
            abort(404);
        }

        $this->recurringInvoiceService->cancel($recurringInvoice);

        return back()->with('success', 'Recurring invoice cancelled');
    }

    public function generateNow(Request $request, int $id)
    {
        $companyId = $request->user()->cmsUser->company_id;
        $recurringInvoice = $this->recurringInvoiceService->getById($id, $companyId);

        if (!$recurringInvoice) {
            abort(404);
        }

        $invoice = $this->recurringInvoiceService->generateInvoice($recurringInvoice);

        if ($invoice) {
            return redirect()->route('cms.invoices.show', $invoice->id)
                ->with('success', 'Invoice generated successfully');
        }

        return back()->with('error', 'Failed to generate invoice');
    }
}
