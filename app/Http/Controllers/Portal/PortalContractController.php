<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\ContractModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalContractController extends Controller
{
    public function index(Request $request): Response
    {
        $customerIds = $request->user()->portalCustomers()->pluck('customer_id');

        $contracts = ContractModel::whereIn('customer_id', $customerIds)
            ->with(['company', 'template'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Portal/Contracts', ['contracts' => $contracts]);
    }

    public function show(Request $request, int $id): Response
    {
        $customerIds = $request->user()->portalCustomers()->pluck('customer_id');

        $contract = ContractModel::whereIn('customer_id', $customerIds)
            ->with(['company', 'template', 'signatures', 'renewals.renewedContract'])
            ->findOrFail($id);

        return Inertia::render('Portal/ContractDetail', ['contract' => $contract]);
    }
}
