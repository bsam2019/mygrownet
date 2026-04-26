<?php

namespace App\Http\Controllers\CMS;

use App\Domain\CMS\Core\Services\InvoiceService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use App\Infrastructure\Persistence\Eloquent\CMS\MeasurementRecordModel;
use App\Infrastructure\Persistence\Eloquent\CMS\QuotationModel;
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
        $cmsUser  = $request->user()->cmsUser;
        $company  = $cmsUser->company;
        $companyId = $company->id;

        // Invoice summary
        $invoiceSummary = $this->invoiceService->getInvoiceSummary($companyId);

        // Core stats (every tenant)
        $stats = [
            'activeJobs'      => JobModel::forCompany($companyId)->whereIn('status', ['pending', 'in_progress'])->count(),
            'totalCustomers'  => CustomerModel::forCompany($companyId)->active()->count(),
            'pendingInvoices' => $invoiceSummary['sent_count'] + $invoiceSummary['partial_count'],
            'monthlyRevenue'  => JobModel::forCompany($companyId)
                ->where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->whereYear('completed_at', now()->year)
                ->sum('actual_value'),
            'totalOutstanding' => $invoiceSummary['total_outstanding'],
        ];

        // Fabrication stats — only when module is enabled for this tenant
        $hasFabrication = $company->hasFabricationModule();
        $recentMeasurements = collect();

        if ($hasFabrication) {
            $stats['pendingMeasurements']   = MeasurementRecordModel::where('company_id', $companyId)->where('status', 'draft')->count();
            $stats['completedMeasurements'] = MeasurementRecordModel::where('company_id', $companyId)->where('status', 'completed')->count();
            $stats['pendingQuotations']     = QuotationModel::where('company_id', $companyId)->where('status', 'draft')->count();
            $stats['fabricationJobs']       = JobModel::forCompany($companyId)
                ->whereIn('status', ['materials_ordered', 'fabrication', 'ready_for_install', 'installing'])
                ->count();

            $recentMeasurements = MeasurementRecordModel::with(['customer'])
                ->where('company_id', $companyId)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        // Recent jobs
        $recentJobs = JobModel::with(['customer'])
            ->forCompany($companyId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent invoices
        $recentInvoices = InvoiceModel::with(['customer'])
            ->where('company_id', $companyId)
            ->orderBy('invoice_date', 'desc')
            ->limit(5)
            ->get();

        // Customers for quick forms
        $customers = CustomerModel::forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'customer_number', 'name', 'email', 'phone', 'outstanding_balance']);

        return Inertia::render('CMS/Dashboard', [
            'company'            => $company,
            'user'               => $request->user(),
            'cmsUser'            => $cmsUser->load('role'),
            'stats'              => $stats,
            'recentJobs'         => $recentJobs,
            'recentInvoices'     => $recentInvoices,
            'invoiceSummary'     => $invoiceSummary,
            'customers'          => $customers,
            'hasFabrication'     => $hasFabrication,
            'recentMeasurements' => $recentMeasurements,
        ]);
    }
}
