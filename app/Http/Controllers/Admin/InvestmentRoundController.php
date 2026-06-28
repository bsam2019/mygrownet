<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Investor\Entities\InvestmentRound;
use App\Domain\Investor\Repositories\InvestmentRoundRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin Investment Round Controller
 * 
 * Manages investment rounds for the investor dashboard
 */
class InvestmentRoundController extends Controller
{
    public function __construct(
        private readonly InvestmentRoundRepositoryInterface $repository
    ) {}

    /**
     * Display list of investment rounds
     */
    public function index(): Response
    {
        $rounds = $this->repository->getAll();
        
        return Inertia::render('Admin/Investor/InvestmentRounds/Index', [
            'rounds' => array_map(fn($round) => $this->formatRound($round), $rounds),
        ]);
    }

    /**
     * Show create form
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Investor/InvestmentRounds/Create');
    }

    /**
     * Store new investment round
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'goal_amount' => 'required|numeric|min:0',
            'minimum_investment' => 'required|numeric|min:0',
            'valuation' => 'required|numeric|min:0',
            'equity_percentage' => 'required|numeric|min:0|max:100',
            'expected_roi' => 'required|string|max:50',
            'use_of_funds' => 'required|array',
            'use_of_funds.*.label' => 'required|string',
            'use_of_funds.*.percentage' => 'required|numeric|min:0|max:100',
            'use_of_funds.*.amount' => 'required|numeric|min:0',
        ]);

        $round = InvestmentRound::create(
            name: $validated['name'],
            description: $validated['description'],
            goalAmount: $validated['goal_amount'],
            minimumInvestment: $validated['minimum_investment'],
            valuation: $validated['valuation'],
            equityPercentage: $validated['equity_percentage'],
            expectedRoi: $validated['expected_roi'],
            useOfFunds: $validated['use_of_funds']
        );

        $this->repository->save($round);

        return redirect()->route('admin.investment-rounds.index')
            ->with('success', 'Investment round created successfully');
    }

    /**
     * Show edit form
     */
    public function edit(int $id): Response
    {
        $round = $this->repository->findById($id);
        
        if (!$round) {
            abort(404);
        }

        return Inertia::render('Admin/Investor/InvestmentRounds/Edit', [
            'round' => $this->formatRound($round),
        ]);
    }

    /**
     * Update investment round
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'goal_amount' => 'required|numeric|min:0',
            'minimum_investment' => 'required|numeric|min:0',
            'valuation' => 'required|numeric|min:0',
            'equity_percentage' => 'required|numeric|min:0|max:100',
            'expected_roi' => 'required|string|max:50',
            'use_of_funds' => 'required|array',
        ]);

        $round = $this->repository->findById($id);
        
        if (!$round) {
            abort(404);
        }

        $round->updateDetails(
            name: $validated['name'],
            description: $validated['description'],
            goalAmount: $validated['goal_amount'],
            minimumInvestment: $validated['minimum_investment'],
            valuation: $validated['valuation'],
            equityPercentage: $validated['equity_percentage'],
            expectedRoi: $validated['expected_roi'],
            useOfFunds: $validated['use_of_funds']
        );

        $this->repository->save($round);

        return redirect()->route('admin.investment-rounds.index')
            ->with('success', 'Investment round updated successfully');
    }

    /**
     * Activate investment round
     */
    public function activate(int $id)
    {
        $round = $this->repository->findById($id);
        
        if (!$round) {
            abort(404);
        }

        $round->activate();
        $this->repository->save($round);

        return back()->with('success', 'Investment round activated');
    }

    /**
     * Set as featured
     */
    public function setFeatured(int $id)
    {
        // Remove featured from all other rounds
        $allRounds = $this->repository->getAll();
        foreach ($allRounds as $r) {
            if ($r->isFeatured()) {
                $r->removeFeatured();
                $this->repository->save($r);
            }
        }

        // Set this round as featured
        $round = $this->repository->findById($id);
        
        if (!$round) {
            abort(404);
        }

        $round->setAsFeatured();
        $this->repository->save($round);

        return back()->with('success', 'Investment round set as featured');
    }

    /**
     * Close investment round
     */
    public function close(int $id)
    {
        $round = $this->repository->findById($id);
        
        if (!$round) {
            abort(404);
        }

        $round->close();
        $this->repository->save($round);

        return back()->with('success', 'Investment round closed');
    }

    /**
     * Reopen investment round
     */
    public function reopen(int $id)
    {
        $round = $this->repository->findById($id);
        
        if (!$round) {
            abort(404);
        }

        $round->activate();
        $this->repository->save($round);

        return back()->with('success', 'Investment round reopened and activated');
    }

    /**
     * Delete investment round
     */
    public function destroy(int $id)
    {
        $this->repository->delete($id);

        return back()->with('success', 'Investment round deleted');
    }

    private function formatRound($round): array
    {
        return [
            'id' => $round->getId(),
            'name' => $round->getName(),
            'description' => $round->getDescription(),
            'goalAmount' => $round->getGoalAmount(),
            'raisedAmount' => $round->getRaisedAmount(),
            'progressPercentage' => $round->getProgressPercentage(),
            'minimumInvestment' => $round->getMinimumInvestment(),
            'valuation' => $round->getValuation(),
            'equityPercentage' => $round->getEquityPercentage(),
            'expectedRoi' => $round->getExpectedRoi(),
            'useOfFunds' => $round->getUseOfFunds(),
            'status' => $round->getStatus()->value(),
            'statusDisplay' => $round->getStatus()->getDisplayName(),
            'statusColor' => $round->getStatus()->getBadgeColor(),
            'isFeatured' => $round->isFeatured(),
            'isActive' => $round->isActive(),
            'isGoalReached' => $round->isGoalReached(),
            'startDate' => $round->getStartDate()?->format('Y-m-d'),
            'endDate' => $round->getEndDate()?->format('Y-m-d'),
            'createdAt' => $round->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
