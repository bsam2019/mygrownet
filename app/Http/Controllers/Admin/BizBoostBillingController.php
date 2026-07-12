<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BillingLedgerService;
use App\Infrastructure\Persistence\Eloquent\BizBoostBillingLedgerModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostOmnichannelLogModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BizBoostBillingController extends Controller
{
    private BillingLedgerService $ledger;

    public function __construct(BillingLedgerService $ledger)
    {
        $this->ledger = $ledger;
    }

    public function index(Request $request)
    {
        $query = BizBoostBillingLedgerModel::with('user');

        if ($search = $request->input('search')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        if ($serviceType = $request->input('service_type')) {
            $query->where('service_type', $serviceType);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(30)->withQueryString();

        $stats = $this->ledger->getGlobalStats();

        return Inertia::render('Admin/BizBoost/Billing', [
            'transactions' => $transactions,
            'stats' => $stats,
            'filters' => $request->only(['search', 'service_type']),
        ]);
    }
}
