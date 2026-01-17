<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Marketplace\Services\PayoutService;
use App\Models\MarketplacePayout;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MarketplacePayoutController extends Controller
{
    public function __construct(
        private PayoutService $payoutService,
    ) {}

    /**
     * Display all payouts
     */
    public function index(Request $request)
    {
        $filters = [
            'status' => $request->status,
            'payout_method' => $request->payout_method,
            'seller_id' => $request->seller_id,
        ];

        $payouts = $this->payoutService->getAllPayouts($filters, 20);
        $stats = $this->payoutService->getPayoutStats();

        return Inertia::render('Admin/Marketplace/Payouts/Index', [
            'payouts' => $payouts,
            'stats' => $stats,
            'filters' => [
                'status' => $request->status ?? '',
                'payout_method' => $request->payout_method ?? '',
            ],
        ]);
    }

    /**
     * Show payout details
     */
    public function show(int $id)
    {
        $payout = MarketplacePayout::with(['seller.user', 'approvedBy', 'processedBy'])
            ->findOrFail($id);

        return Inertia::render('Admin/Marketplace/Payouts/Show', [
            'payout' => $payout,
        ]);
    }

    /**
     * Approve payout
     */
    public function approve(Request $request, int $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->payoutService->approvePayout($id, $request->user()->id, $validated['notes'] ?? null);

            return back()->with('success', 'Payout approved successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reject payout
     */
    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->payoutService->rejectPayout($id, $request->user()->id, $validated['reason']);

            return back()->with('success', 'Payout rejected.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mark payout as processing
     */
    public function process(Request $request, int $id)
    {
        try {
            $this->payoutService->markAsProcessing($id, $request->user()->id);

            return back()->with('success', 'Payout marked as processing.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Complete payout
     */
    public function complete(Request $request, int $id)
    {
        $validated = $request->validate([
            'transaction_reference' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $metadata = $validated['notes'] ? ['notes' => $validated['notes']] : null;
            $this->payoutService->completePayout($id, $validated['transaction_reference'], $metadata);

            return back()->with('success', 'Payout completed successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mark payout as failed
     */
    public function fail(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->payoutService->markAsFailed($id, $validated['reason']);

            return back()->with('success', 'Payout marked as failed. Amount refunded to seller.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
