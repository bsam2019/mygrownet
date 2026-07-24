<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Investor\Concerns\RequiresInvestorAuth;
use App\Domain\Investor\Services\LiquidityEventService;
use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;
use App\Models\LiquidityEvent;
use App\Models\LiquidityEventParticipation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LiquidityEventController extends Controller
{
    use RequiresInvestorAuth;

    public function __construct(
        private LiquidityEventService $liquidityService,
        private InvestorAccountRepositoryInterface $accountRepository
    ) {}

    public function index(Request $request)
    {
        $domainInvestor = $this->requireInvestorAuth();

        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $investor = $this->accountRepository->findById($domainInvestor->getId());
        $events = $this->liquidityService->getActiveEvents();
        $participations = $this->liquidityService->getInvestorParticipations($investor);

        return Inertia::render('Investor/LiquidityEvents', [
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
            ],
            'events' => $events,
            'participations' => $participations,
            'currentShares' => $investor->getEquityPercentage(),
            'activePage' => 'liquidity-events',
            'unreadMessages' => 0,
            'unreadAnnouncements' => 0,
        ]);
    }

    public function show(Request $request, LiquidityEvent $event)
    {
        $domainInvestor = $this->requireInvestorAuth();

        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $investor = $this->accountRepository->findById($domainInvestor->getId());
        $data = $this->liquidityService->getEventWithParticipation($event->id, $investor);

        return Inertia::render('Investor/LiquidityEventDetail', [
            ...$data,
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
            ],
            'activePage' => 'liquidity-events',
        ]);
    }

    public function registerInterest(Request $request, LiquidityEvent $event)
    {
        $domainInvestor = $this->requireInvestorAuth();

        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $investor = $this->accountRepository->findById($domainInvestor->getId());

        try {
            $this->liquidityService->registerInterest($event, $investor);
            return back()->with('success', 'Interest registered successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

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

        $investor = $this->accountRepository->findById($domainInvestor->getId());

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

    public function withdraw(Request $request, LiquidityEventParticipation $participation)
    {
        $domainInvestor = $this->requireInvestorAuth();

        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $investor = $this->accountRepository->findById($domainInvestor->getId());

        if ($participation->investor_account_id !== $investor->getId()) {
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
