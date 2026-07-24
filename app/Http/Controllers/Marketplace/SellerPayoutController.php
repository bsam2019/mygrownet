<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Domain\Marketplace\Services\SellerService;
use App\Domain\Marketplace\Services\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SellerPayoutController extends Controller
{
    public function __construct(
        private SellerService $sellerService,
        private PayoutService $payoutService,
    ) {}

    public function index(Request $request)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);

        if (!$seller) {
            return redirect()->route('marketplace.seller.register');
        }

        $payouts = $this->payoutService->getSellerPayouts($seller['id'], 20);
        $availableBalance = $this->payoutService->getAvailableBalance($seller['id']);
        $minimumPayout = $this->payoutService->getMinimumPayoutAmount();
        $canRequest = $this->payoutService->canRequestPayout($seller['id']);

        return Inertia::render('Marketplace/Seller/Payouts/Index', [
            'payouts' => $payouts,
            'availableBalance' => $availableBalance,
            'minimumPayout' => $minimumPayout,
            'canRequest' => $canRequest,
            'payoutMethods' => config('marketplace.payouts.methods'),
        ]);
    }

    public function create(Request $request)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);

        if (!$seller) {
            return redirect()->route('marketplace.seller.register');
        }

        $availableBalance = $this->payoutService->getAvailableBalance($seller['id']);
        $minimumPayout = $this->payoutService->getMinimumPayoutAmount();
        $canRequest = $this->payoutService->canRequestPayout($seller['id']);

        if (!$canRequest['can_request']) {
            return redirect()->route('marketplace.seller.payouts.index')
                ->withErrors(['error' => $canRequest['reason']]);
        }

        return Inertia::render('Marketplace/Seller/Payouts/Request', [
            'availableBalance' => $availableBalance,
            'minimumPayout' => $minimumPayout,
            'payoutMethods' => config('marketplace.payouts.methods'),
            'seller' => $seller,
        ]);
    }

    public function store(Request $request)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);

        if (!$seller) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|integer|min:1',
            'payout_method' => 'required|in:momo,airtel,bank',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:255',
            'bank_name' => 'required_if:payout_method,bank|nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $payout = $this->payoutService->createPayoutRequest($seller['id'], $validated);

            return redirect()->route('marketplace.seller.payouts.index')
                ->with('success', 'Payout request submitted successfully. Reference: ' . $payout['reference']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show(Request $request, int $id)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);

        if (!$seller) {
            abort(403);
        }

        $payout = DB::table('marketplace_payouts')
            ->leftJoin('users as approved_by', 'marketplace_payouts.approved_by', '=', 'approved_by.id')
            ->leftJoin('users as processed_by', 'marketplace_payouts.processed_by', '=', 'processed_by.id')
            ->where('marketplace_payouts.id', $id)
            ->where('marketplace_payouts.seller_id', $seller['id'])
            ->select(
                'marketplace_payouts.*',
                'approved_by.name as approved_by_name',
                'processed_by.name as processed_by_name'
            )
            ->first();

        if (!$payout) {
            abort(404);
        }

        $payout = (array) $payout;

        return Inertia::render('Marketplace/Seller/Payouts/Show', [
            'payout' => $payout,
        ]);
    }
}
