<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalInvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $customerIds = $request->user()->portalCustomers()->pluck('customer_id');

        $invoices = InvoiceModel::whereIn('customer_id', $customerIds)
            ->with(['items', 'payments', 'company'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Portal/Invoices', ['invoices' => $invoices]);
    }

    public function show(Request $request, int $id): Response
    {
        $customerIds = $request->user()->portalCustomers()->pluck('customer_id');

        $invoice = InvoiceModel::whereIn('customer_id', $customerIds)
            ->with(['items', 'payments', 'company', 'job'])
            ->findOrFail($id);

        return Inertia::render('Portal/InvoiceDetail', ['invoice' => $invoice]);
    }
}
