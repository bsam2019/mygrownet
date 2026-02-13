<?php

namespace App\Http\Controllers\CMS;

use App\Domain\CMS\Core\Services\InvoiceService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService
    ) {}

    public function index(Request $request): Response
    {
        $cmsUser = $request->user()->cmsUser;
        $company = $cmsUser->company;

        // Get invoice summary
        $invoiceSummary = $this->invoiceService->getInvoiceSummary($company->id);

        // Get stats
        $stats = [
            'activeJobs' => JobModel::forCompany($company->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'totalCustomers' => CustomerModel::forCompany($company->id)
                ->active()
                ->count(),
            'pendingInvoices' => $invoiceSummary['sent_count'] + $invoiceSummary['partial_count'],
            'monthlyRevenue' => JobModel::forCompany($company->id)
                ->where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->whereYear('completed_at', now()->year)
                ->sum('actual_value'),
            'totalOutstanding' => $invoiceSummary['total_outstanding'],
        ];

        // Get recent jobs
        $recentJobs = JobModel::with(['customer'])
            ->forCompany($company->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get recent invoices
        $recentInvoices = InvoiceModel::with(['customer'])
            ->where('company_id', $company->id)
            ->orderBy('invoice_date', 'desc')
            ->limit(5)
            ->get();

        // Get customers for quick forms
        $customers = CustomerModel::forCompany($company->id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'customer_number', 'name', 'email', 'phone', 'outstanding_balance']);

        return Inertia::render('CMS/Dashboard', [
            'company' => $company,
            'user' => $request->user(),
            'cmsUser' => $cmsUser->load('role'),
            'stats' => $stats,
            'recentJobs' => $recentJobs,
            'recentInvoices' => $recentInvoices,
            'invoiceSummary' => $invoiceSummary,
            'customers' => $customers,
        ]);
    }
}
