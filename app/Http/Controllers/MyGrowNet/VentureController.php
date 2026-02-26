<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureDocumentModel;
use App\Models\Receipt;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class VentureController extends Controller
{
    /**
     * Display venture marketplace
     */
    public function index(Request $request): Response
    {
        $ventures = VentureModel::with(['category', 'creator'])
            ->where('status', 'funding')
            ->when($request->category, function ($query, $category) {
                $query->whereHas('category', function ($q) use ($category) {
                    $q->where('slug', $category);
                });
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

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

        // Check investment limits
        if ($venture->maximum_investment && $request->amount > $venture->maximum_investment) {
            return back()->with('error', 'Investment amount exceeds maximum allowed.');
        }

        // Check remaining funding
        $remainingFunding = $venture->funding_target - $venture->total_raised;
        if ($request->amount > $remainingFunding) {
            return back()->with('error', 'Investment amount exceeds remaining funding needed.');
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

        // Process payment based on method
        if ($request->payment_method === 'wallet') {
            return $this->processWalletInvestment($request, $venture);
        } else {
            return $this->processMobileMoneyInvestment($request, $venture);
        }
    }

    /**
     * Process wallet investment
     */
    private function processWalletInvestment(Request $request, VentureModel $venture)
    {
        $user = auth()->user();
        $amount = $request->amount;

        // Check wallet balance
        if ($user->wallet_balance < $amount) {
            return back()->with('error', 'Insufficient wallet balance.');
        }

        DB::beginTransaction();
        try {
            // Create investment record
            $investment = VentureInvestmentModel::create([
                'user_id' => $user->id,
                'venture_id' => $venture->id,
                'amount' => $amount,
                'shares' => $this->calculateShares($amount, $venture),
                'payment_method' => 'wallet',
                'payment_reference' => 'WALLET_' . strtoupper(Str::random(10)),
                'status' => 'confirmed', // Wallet payments are instant
                'payment_confirmed_at' => now(),
            ]);

            // Deduct from wallet
            $user->decrement('wallet_balance', $amount);

            // Update venture totals
            $venture->increment('total_raised', $amount);
            $venture->increment('investor_count');

            // Create receipt using existing system
            $receiptService = app(ReceiptService::class);
            $receiptService->createReceipt([
                'user_id' => $user->id,
                'type' => 'venture_investment',
                'amount' => $amount,
                'description' => "Investment in {$venture->title}",
                'reference_id' => $investment->id,
                'payment_method' => 'wallet',
                'payment_reference' => $investment->payment_reference,
            ]);

            // Check if venture is fully funded
            if ($venture->total_raised >= $venture->funding_target) {
                $venture->update(['status' => 'funded']);
            }

            DB::commit();

            return redirect()->route('mygrownet.ventures.investment-success', $investment->id)
                ->with('success', 'Investment successful! You are now a shareholder.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Investment failed. Please try again.');
        }
    }

    /**
     * Process mobile money investment
     */
    private function processMobileMoneyInvestment(Request $request, VentureModel $venture)
    {
        $user = auth()->user();
        $amount = $request->amount;

        DB::beginTransaction();
        try {
            // Create pending investment record
            $investment = VentureInvestmentModel::create([
                'user_id' => $user->id,
                'venture_id' => $venture->id,
                'amount' => $amount,
                'shares' => $this->calculateShares($amount, $venture),
                'payment_method' => 'mobile_money',
                'payment_reference' => 'MM_' . strtoupper(Str::random(10)),
                'status' => 'pending',
            ]);

            DB::commit();

            // Redirect to payment page (using existing payment system)
            return redirect()->route('mygrownet.submit-payment')
                ->with([
                    'payment_data' => [
                        'amount' => $amount,
                        'type' => 'venture_investment',
                        'description' => "Investment in {$venture->title}",
                        'reference_id' => $investment->id,
                        'return_url' => route('mygrownet.ventures.investment-success', $investment->id),
                    ]
                ]);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to initiate payment. Please try again.');
        }
    }

    /**
     * Calculate shares based on investment amount
     */
    private function calculateShares(float $amount, VentureModel $venture): int
    {
        // Simple calculation: 1 share per K100 invested
        // This can be made more sophisticated later
        return (int) floor($amount / 100);
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

        $totalInvested = $investments->sum('amount');
        $activeVentures = $investments->where('venture.status', 'active')->count();
        
        return Inertia::render('GrowNet/Ventures/Portfolio', [
            'investments' => $investments,
            'totalInvested' => $totalInvested,
            'activeVentures' => $activeVentures,
        ]);
    }

    /**
     * Display user's dividend history
     */
    public function dividends(): Response
    {
        // Get all dividends for user's shareholdings
        $dividends = auth()->user()->load([
            'ventureInvestments.shareholder.dividends' => function ($query) {
                $query->with('venture')->orderBy('payment_date', 'desc');
            }
        ]);

        return Inertia::render('GrowNet/Ventures/Dividends', [
            'dividends' => $dividends,
        ]);
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
