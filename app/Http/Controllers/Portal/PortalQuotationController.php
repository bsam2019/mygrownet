<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\QuotationModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalQuotationController extends Controller
{
    public function index(Request $request): Response
    {
        $customerIds = $request->user()->portalCustomers()->pluck('customer_id');

        $quotes = QuotationModel::whereIn('customer_id', $customerIds)
            ->with(['items', 'company'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Portal/Quotes', ['quotes' => $quotes]);
    }

    public function show(Request $request, int $id): Response
    {
        $customerIds = $request->user()->portalCustomers()->pluck('customer_id');

        $quote = QuotationModel::whereIn('customer_id', $customerIds)
            ->with(['items', 'company'])
            ->findOrFail($id);

        return Inertia::render('Portal/QuoteDetail', ['quote' => $quote]);
    }
}
