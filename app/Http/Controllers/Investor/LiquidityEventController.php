<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Investor\Concerns\RequiresInvestorAuth;
use App\Domain\Investor\Services\LiquidityEventService;
use App\Models\LiquidityEvent;
use App\Models\LiquidityEventParticipation;
use App\Models\InvestorAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LiquidityEventController extends Controller
{
    use RequiresInvestorAuth;

    public function __construct(
        private LiquidityEventService $liquidityService
    ) {}

    /**
     * Display liquidity events page
     */
    public function index(Request $request)
    {
        $domainInvestor = $this->requireInvestorAuth();
        
        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $investor = InvestorAccount::find($domainInvestor->getId());
        $events = $this->liquidityService->getActiveEvents();
        $participations = $this->liquidityService->getInvestorParticipations($investor);

        return Inertia::render('Investor/LiquidityEvents', [
            'investor' => [
                'id' => $investor->id,
                'name' => $investor->name,
                'email' => $investor->email,
            ],
            'events' => $events,
            'participations' => $participations,
            'currentShares' => $investor->equity_percentage,
            'activePage' => 'liquidity-events',
            'unreadMessages' => 0,
            'unreadAnnouncements' => 0,
        ]);
    }

    /**
     * Show event details
     */
    public function show(Request $request, LiquidityEvent $event)
    {
        $domainInvestor = $this->requireInvestorAuth();
        
        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $investor = InvestorAccount::find($domainInvestor->getId());
        $data = $this->liquidityService->getEventWithParticipation($event->id, $investor);

        return Inertia::render('Investor/LiquidityEventDetail', [
            ...$data,
            'investor' => [
                'id' => $investor->id,
                'name' => $investor->name,
                'email' => $investor->email,
            ],
            'activePage' => 'liquidity-events',
        ]);
    }

    /**
     * Register interest in an event
     */
    public function registerInterest(Request $request, LiquidityEvent $event)
    {
        $domainInvestor = $this->requireInvestorAuth();
        
        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $investor = InvestorAccount::find($domainInvestor->getId());

        try {
            $this->liquidityService->registerInterest($event, $investor);
            return back()->with('success', 'Interest registered successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Register for participation
     */
    public function register(Request $request, LiquidityEvent $event)
    {
        $domainInvestor = $this->requireInvestorAuth();
        
        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $validated = $request->validate([
            'shares_offered' => 'required|numeric|min:0.0001',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
        ]);

        $investor = InvestorAccount::find($domainInvestor->getId());

        $bankDetails = null;
        if ($validated['bank_name'] ?? null) {
            $bankDetails = [
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'account_name' => $validated['account_name'],
            ];
        }

        try {
            $this->liquidityService->registerParticipation(
                event: $event,
                investor: $investor,
                sharesOffered: $validated['shares_offered'],
                bankDetails: $bankDetails
            );
            return back()->with('success', 'Registration submitted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Withdraw from participation
     */
    public function withdraw(Request $request, LiquidityEventParticipation $participation)
    {
        $domainInvestor = $this->requireInvestorAuth();
        
        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $investor = InvestorAccount::find($domainInvestor->getId());

        if ($participation->investor_account_id !== $investor->id) {
            abort(403);
        }

        try {
            $this->liquidityService->withdrawParticipation($participation);
            return back()->with('success', 'Participation withdrawn.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
