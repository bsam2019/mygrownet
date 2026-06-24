<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureDocumentModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareholderModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureDividendModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareTransferModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureResolutionModel;
use App\Models\Receipt;
use App\Services\ReceiptService;
use App\Services\VentureBuilder\VentureCacheService;
use App\Services\VentureBuilder\VentureService;
use App\Services\VentureBuilder\VentureInvestmentService;
use App\Services\VentureBuilder\VentureKycService;
use App\Services\VentureBuilder\VentureLockInService;
use App\Services\VentureBuilder\VentureShareTransferService;
use App\Services\VentureBuilder\VentureVoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class VentureController extends Controller
{
    public function __construct(
        private readonly VentureCacheService $cacheService,
        private readonly VentureService $ventureService,
        private readonly VentureInvestmentService $investmentService,
        private readonly VentureKycService $kycService,
        private readonly VentureLockInService $lockInService,
        private readonly VentureShareTransferService $shareTransferService,
        private readonly VentureVoteService $voteService,
    ) {}

    /**
     * Display venture marketplace (cached)
     */
    public function index(Request $request): Response
    {
        $ventures = $this->cacheService->getVenturesMarketplace(
            $request->category,
            $request->search
        );

        return Inertia::render('GrowNet/Ventures/Index', [
            'ventures' => $ventures,
            'filters' => $request->only(['category', 'search']),
        ]);
    }

    /**
     * Display venture details
     */
    public function show(VentureModel $venture): Response
    {
        $venture->load([
            'category',
            'creator',
            'documents' => function ($query) {
                $query->forInvestors()->orderBy('created_at', 'desc');
            },
            'updates' => function ($query) {
                $query->published()->forInvestors()->orderBy('published_at', 'desc')->limit(5);
            }
        ]);

        // Increment view count
        $venture->increment('views_count');

        // Check if user has invested
        $userInvestment = null;
        if (auth()->check()) {
            $userInvestment = VentureInvestmentModel::where('venture_id', $venture->id)
                ->where('user_id', auth()->id())
                ->first();
        }

        return Inertia::render('GrowNet/Ventures/Show', [
            'venture' => $venture,
            'userInvestment' => $userInvestment,
            'fundingProgress' => $venture->getFundingProgressPercentage(),
        ]);
    }

    /**
     * Show investment form
     */
    public function showInvestForm(VentureModel $venture): Response
    {
        // Check if venture can accept investments
        if ($venture->status !== 'funding') {
            return redirect()->route('ventures.show', $venture->slug)
                ->with('error', 'This venture is not currently accepting investments.');
        }

        $venture->load('category');

        return Inertia::render('GrowNet/Ventures/Invest', [
            'venture' => $venture,
            'walletBalance' => auth()->user()->wallet_balance ?? 0,
            'fundingProgress' => ($venture->total_raised / $venture->funding_target) * 100,
        ]);
    }

    /**
     * Make an investment
     */
    public function invest(Request $request, VentureModel $venture)
    {
        $request->validate([
            'amount' => 'required|numeric|min:' . $venture->minimum_investment,
            'payment_method' => 'required|in:wallet,mobile_money',
        ]);

        // Check if venture can accept investments
        if ($venture->status !== 'funding') {
            return back()->with('error', 'This venture is not currently accepting investments.');
        }

        // KYC check for larger investments
        $kycIssues = $this->kycService->canInvest(auth()->user(), $request->amount);
        if (!empty($kycIssues)) {
            return back()->with('error', implode(' ', $kycIssues));
        }

        // Check for duplicate investment (prevent double-clicking)
        $existingInvestment = VentureInvestmentModel::where('user_id', auth()->id())
            ->where('venture_id', $venture->id)
            ->where('status', 'pending')
            ->where('created_at', '>', now()->subMinutes(5))
            ->first();

        if ($existingInvestment) {
            return back()->with('error', 'You have a pending investment. Please wait before trying again.');
        }

        try {
            if ($request->payment_method === 'wallet') {
                $investment = $this->investmentService->processWalletInvestment(
                    auth()->user(),
                    $venture,
                    $request->amount
                );

                return redirect()->route('mygrownet.ventures.investment-success', $investment->id)
                    ->with('success', 'Investment successful! You are now a shareholder.');
            } else {
                $investment = $this->investmentService->initiateMobileMoneyInvestment(
                    auth()->user(),
                    $venture,
                    $request->amount
                );

                return redirect()->route('mygrownet.submit-payment')
                    ->with([
                        'payment_data' => [
                            'amount' => $request->amount,
                            'type' => 'venture_investment',
                            'description' => "Investment in {$venture->title}",
                            'reference_id' => $investment->id,
                            'return_url' => route('mygrownet.ventures.investment-success', $investment->id),
                        ]
                    ]);
            }
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Investment failed. Please try again.');
        }
    }

    /**
     * Show investment success page
     */
    public function investmentSuccess(VentureInvestmentModel $investment): Response
    {
        // Ensure user owns this investment
        if ($investment->user_id !== auth()->id()) {
            abort(403);
        }

        $investment->load('venture');

        // Get receipt if exists
        $receipt = Receipt::where('reference_id', $investment->id)
            ->where('type', 'venture_investment')
            ->first();

        return Inertia::render('GrowNet/Ventures/InvestmentSuccess', [
            'investment' => $investment,
            'receipt' => $receipt,
        ]);
    }

    /**
     * Display user's investments
     */
    public function myInvestments(): Response
    {
        $investments = VentureInvestmentModel::with(['venture.category'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('GrowNet/Ventures/MyInvestments', [
            'investments' => $investments,
        ]);
    }

    /**
     * Display investment details
     */
    public function investmentDetails(VentureInvestmentModel $investment): Response
    {
        // Ensure user owns this investment
        if ($investment->user_id !== auth()->id()) {
            abort(403);
        }

        $investment->load([
            'venture.category',
            'venture.updates' => function ($query) {
                $query->published()->forInvestors()->orderBy('published_at', 'desc');
            },
            'shareholder.dividends' => function ($query) {
                $query->orderBy('payment_date', 'desc');
            }
        ]);

        return Inertia::render('GrowNet/Ventures/InvestmentDetails', [
            'investment' => $investment,
        ]);
    }

    /**
     * Display user's portfolio
     */
    public function portfolio(): Response
    {
        $user = auth()->user();
        
        $investments = VentureInvestmentModel::with(['venture'])
            ->where('user_id', $user->id)
            ->confirmed()
            ->get();

        $shareholders = VentureShareholderModel::with(['venture', 'investment'])
            ->where('user_id', $user->id)
            ->active()
            ->get();

        $totalInvested = $investments->sum('amount');
        $totalShares = $investments->sum('shares_allocated');
        $activeVentures = $investments->where('venture.status', 'active')->count();
        $totalDividendsReceived = $shareholders->sum('total_dividends_received');
        
        $ventures = [
            'total' => $investments->count(),
            'active' => $investments->whereIn('venture.status', ['active', 'funded'])->count(),
            'funded' => $investments->where('venture.status', 'funded')->count(),
        ];
        
        return Inertia::render('GrowNet/Ventures/Portfolio', [
            'investments' => $investments,
            'shareholders' => $shareholders,
            'totalInvested' => $totalInvested,
            'totalShares' => $totalShares,
            'activeVentures' => $activeVentures,
            'totalDividendsReceived' => $totalDividendsReceived,
            'ventures' => $ventures,
        ]);
    }

    /**
     * Display user's dividend history
     */
    public function dividends(): Response
    {
        $user = auth()->user();
        
        $shareholders = VentureShareholderModel::where('user_id', $user->id)
            ->active()
            ->pluck('id');

        $dividends = VentureDividendModel::with(['venture', 'shareholder'])
            ->whereIn('shareholder_id', $shareholders)
            ->orderBy('declaration_date', 'desc')
            ->get();

        $totalEarned = $dividends->sum('amount');
        $totalPaid = $dividends->where('status', 'paid')->sum('amount');
        $totalPending = $dividends->whereIn('status', ['declared', 'processing'])->sum('amount');

        return Inertia::render('GrowNet/Ventures/Dividends', [
            'dividends' => $dividends,
            'totalEarned' => $totalEarned,
            'totalPaid' => $totalPaid,
            'totalPending' => $totalPending,
        ]);
    }

    /**
     * Request withdrawal of an investment (subject to lock-in)
     */
    public function withdraw(VentureInvestmentModel $investment)
    {
        if ($investment->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$investment->isConfirmed()) {
            return back()->with('error', 'Only confirmed investments can be withdrawn.');
        }

        try {
            $this->lockInService->assertNotLocked($investment);

            $this->investmentService->refundInvestment($investment);

            return back()->with('success', 'Withdrawal request processed successfully. Funds have been returned to your wallet.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Withdrawal failed. Please contact support.');
        }
    }

    /**
     * Request a share transfer
     */
    public function requestTransfer(Request $request, VentureModel $venture)
    {
        $validated = $request->validate([
            'to_user_email' => 'required|email|exists:users,email',
            'shares' => 'required|numeric|min:1',
            'price_per_share' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        $toUser = \App\Models\User::where('email', $validated['to_user_email'])->firstOrFail();

        if ($toUser->id === auth()->id()) {
            return back()->with('error', 'You cannot transfer shares to yourself.');
        }

        try {
            $transfer = $this->shareTransferService->requestTransfer(
                auth()->user(),
                $venture,
                $toUser,
                $validated['shares'],
                $validated['price_per_share'] ?? null,
                $validated['reason'] ?? null,
            );

            return back()->with('success', 'Transfer request submitted and is pending admin approval.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * View my transfers
     */
    public function myTransfers(): Response
    {
        $userId = auth()->id();

        $transfers = VentureShareTransferModel::with(['venture', 'fromUser', 'toUser'])
            ->where(function ($q) use ($userId) {
                $q->where('from_user_id', $userId)->orWhere('to_user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('GrowNet/Ventures/Transfers', [
            'transfers' => $transfers,
        ]);
    }

    /**
     * Show resolutions for a venture
     */
    public function resolutions(VentureModel $venture): Response
    {
        $resolutions = VentureResolutionModel::with(['creator'])
            ->where('venture_id', $venture->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $myShareholder = VentureShareholderModel::where('venture_id', $venture->id)
            ->where('user_id', auth()->id())
            ->active()
            ->first();

        $myVotes = [];
        if ($myShareholder) {
            $myVotes = VentureResolutionModel::where('venture_id', $venture->id)
                ->get()
                ->keyBy('id')
                ->map(function ($res) use ($myShareholder) {
                    return \App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureVoteModel::where('resolution_id', $res->id)
                        ->where('shareholder_id', $myShareholder->id)
                        ->first();
                })
                ->filter()
                ->toArray();
        }

        return Inertia::render('GrowNet/Ventures/Resolutions', [
            'venture' => $venture,
            'resolutions' => $resolutions,
            'myShareholder' => $myShareholder,
            'myVotes' => $myVotes,
        ]);
    }

    /**
     * Show a specific resolution
     */
    public function showResolution(VentureModel $venture, VentureResolutionModel $resolution): Response
    {
        if ($resolution->venture_id !== $venture->id) {
            abort(404);
        }

        $resolution->load(['votes.shareholder.user', 'creator']);

        $myShareholder = VentureShareholderModel::where('venture_id', $venture->id)
            ->where('user_id', auth()->id())
            ->active()
            ->first();

        $myVote = null;
        if ($myShareholder) {
            $myVote = \App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureVoteModel::where('resolution_id', $resolution->id)
                ->where('shareholder_id', $myShareholder->id)
                ->first();
        }

        return Inertia::render('GrowNet/Ventures/ResolutionDetails', [
            'venture' => $venture,
            'resolution' => $resolution,
            'myShareholder' => $myShareholder,
            'myVote' => $myVote,
        ]);
    }

    /**
     * Cast a vote on a resolution
     */
    public function castVote(Request $request, VentureResolutionModel $resolution)
    {
        $validated = $request->validate([
            'vote' => 'required|in:for,against,abstain',
            'comment' => 'nullable|string|max:500',
        ]);

        try {
            $this->voteService->castVote(
                auth()->user(),
                $resolution,
                $validated['vote'],
                $validated['comment'] ?? null,
            );

            return back()->with('success', 'Your vote has been recorded.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download a document
     */
    public function downloadDocument(VentureModel $venture, VentureDocumentModel $document)
    {
        // Check if user has access
        $userRole = 'public';
        
        if (auth()->check()) {
            $hasInvestment = VentureInvestmentModel::where('venture_id', $venture->id)
                ->where('user_id', auth()->id())
                ->confirmed()
                ->exists();
            
            if ($hasInvestment) {
                $userRole = 'investor';
            }
        }

        if (!$document->canBeAccessedBy($userRole)) {
            abort(403, 'You do not have access to this document.');
        }

        // Increment download count
        $document->increment('download_count');

        return response()->download(storage_path('app/' . $document->file_path));
    }
}
