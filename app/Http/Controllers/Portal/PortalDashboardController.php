<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\ContractModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PaymentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\QuotationModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $customerIds = $user->portalCustomers()->pluck('customer_id');

        $invoices = InvoiceModel::whereIn('customer_id', $customerIds)
            ->latest()->limit(5)->get();

        $quotes = QuotationModel::whereIn('customer_id', $customerIds)
            ->latest()->limit(5)->get();

        $payments = PaymentModel::whereIn('customer_id', $customerIds)
            ->latest()->limit(5)->get();

        $contracts = ContractModel::whereIn('customer_id', $customerIds)
            ->with('company')
            ->latest()->limit(5)->get();

        $stats = [
            'total_invoices' => InvoiceModel::whereIn('customer_id', $customerIds)->count(),
            'paid_invoices' => InvoiceModel::whereIn('customer_id', $customerIds)->where('status', 'paid')->count(),
            'overdue_invoices' => InvoiceModel::whereIn('customer_id', $customerIds)->where('status', 'overdue')->count(),
            'open_quotes' => QuotationModel::whereIn('customer_id', $customerIds)->whereIn('status', ['draft', 'sent'])->count(),
            'outstanding' => InvoiceModel::whereIn('customer_id', $customerIds)->sum('amount_due'),
            'total_contracts' => ContractModel::whereIn('customer_id', $customerIds)->count(),
            'active_contracts' => ContractModel::whereIn('customer_id', $customerIds)->where('status', 'active')->count(),
            'pending_signatures' => ContractModel::whereIn('customer_id', $customerIds)
                ->where('status', 'active')->where('signed_by_customer', false)->count(),
        ];

        $customers = $user->portalCustomers()->with('customer')->get()->pluck('customer');

        return Inertia::render('Portal/Dashboard', [
            'stats' => $stats,
            'invoices' => $invoices,
            'quotes' => $quotes,
            'payments' => $payments,
            'contracts' => $contracts,
            'customers' => $customers,
        ]);
    }
}
