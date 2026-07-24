<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Domain\Investor\Services\ShareTransferService;
use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;
use App\Models\ShareTransferRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShareTransferController extends Controller
{
    public function __construct(
        private ShareTransferService $transferService,
        private InvestorAccountRepositoryInterface $accountRepository
    ) {}

    public function index(Request $request)
    {
        $investorId = session('investor_id');

        if (!$investorId) {
            return redirect()->route('investor.login');
        }

        $investor = $this->accountRepository->findById($investorId);

        if (!$investor) {
            return redirect()->route('investor.login');
        }

        $requests = $this->transferService->getInvestorRequests($investor);

        return Inertia::render('Investor/ShareTransfer', [
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
            ],
            'requests' => $requests,
            'currentShares' => (float) $investor->getEquityPercentage(),
            'investorId' => $investor->getId(),
            'activePage' => 'share-transfer',
            'unreadMessages' => 0,
            'unreadAnnouncements' => 0,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shares_percentage' => 'required|numeric|min:0.0001',
            'proposed_price' => 'required|numeric|min:0',
            'transfer_type' => 'required|in:internal,external,buyback_request',
            'reason_for_sale' => 'required|string|max:1000',
            'proposed_buyer_id' => 'nullable|exists:investor_accounts,id',
            'proposed_buyer_name' => 'nullable|string|max:255',
            'proposed_buyer_email' => 'nullable|email|max:255',
        ]);

        $investorId = session('investor_id');
        if (!$investorId) {
            return redirect()->route('investor.login');
        }

        $investor = $this->accountRepository->findById($investorId);

        if (!$investor) {
            return redirect()->route('investor.login');
        }

        try {
            $this->transferService->createTransferRequest(
                seller: $investor,
                sharesPercentage: $validated['shares_percentage'],
                proposedPrice: $validated['proposed_price'],
                transferType: $validated['transfer_type'],
                reasonForSale: $validated['reason_for_sale'],
                proposedBuyerId: $validated['proposed_buyer_id'] ?? null,
                proposedBuyerName: $validated['proposed_buyer_name'] ?? null,
                proposedBuyerEmail: $validated['proposed_buyer_email'] ?? null
            );

            return back()->with('success', 'Transfer request created as draft.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function submit(Request $request, ShareTransferRequest $transferRequest)
    {
        $investorId = session('investor_id');
        if (!$investorId) {
            return redirect()->route('investor.login');
        }

        $investor = $this->accountRepository->findById($investorId);

        if (!$investor) {
            return redirect()->route('investor.login');
        }

        if ($transferRequest->seller_investor_id !== $investor->getId()) {
            abort(403);
        }

        try {
            $this->transferService->submitForReview($transferRequest);
            return back()->with('success', 'Request submitted for board review.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancel(Request $request, ShareTransferRequest $transferRequest)
    {
        $investorId = session('investor_id');
        if (!$investorId) {
            return redirect()->route('investor.login');
        }

        $investor = $this->accountRepository->findById($investorId);

        if (!$investor) {
            return redirect()->route('investor.login');
        }

        if ($transferRequest->seller_investor_id !== $investor->getId()) {
            abort(403);
        }

        try {
            $this->transferService->cancelRequest($transferRequest);
            return back()->with('success', 'Request cancelled.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
