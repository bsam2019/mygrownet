<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Investor\Entities\InvestorAccount;
use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;
use App\Domain\Investor\Repositories\InvestmentRoundRepositoryInterface;
use App\Domain\Investor\ValueObjects\InvestorStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use DateTimeImmutable;

class InvestorAccountController extends Controller
{
    public function __construct(
        private readonly InvestorAccountRepositoryInterface $accountRepository,
        private readonly InvestmentRoundRepositoryInterface $roundRepository
    ) {}

    public function index()
    {
        $accounts = $this->accountRepository->all();
        
        $accountsData = array_map(function($account) {
            return [
                'id' => $account->getId(),
                'name' => $account->getName(),
                'email' => $account->getEmail(),
                'investment_amount' => $account->getInvestmentAmount(),
                'investment_date' => $account->getInvestmentDate()->format('Y-m-d'),
                'investment_round_id' => $account->getInvestmentRoundId(),
                'status' => $account->getStatus()->value(),
                'status_label' => $account->getStatus()->label(),
                'equity_percentage' => $account->getEquityPercentage(),
                'created_at' => $account->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $accounts);

        return Inertia::render('Admin/Investor/Accounts/Index', [
            'accounts' => $accountsData,
            'totalInvested' => $this->accountRepository->getTotalInvestedAmount(),
            'investorCount' => $this->accountRepository->getInvestorCount(),
        ]);
    }

    public function create()
    {
        $rounds = $this->roundRepository->findActive();
        
        $roundsData = array_map(function($round) {
            return [
                'id' => $round->getId(),
                'name' => $round->getName(),
                'goal_amount' => $round->getGoalAmount(),
                'raised_amount' => $round->getRaisedAmount(),
            ];
        }, $rounds);

        // Get all users for linking
        $users = \App\Models\User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            });

        return Inertia::render('Admin/Investor/Accounts/Create', [
            'rounds' => $roundsData,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'investment_amount' => 'required|numeric|min:0',
            'investment_date' => 'required|date',
            'investment_round_id' => 'required|exists:investment_rounds,id',
            'equity_percentage' => 'nullable|numeric|min:0|max:100',
        ], [
            'name.required' => 'Investor name is required',
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'investment_amount.required' => 'Investment amount is required',
            'investment_amount.numeric' => 'Investment amount must be a number',
            'investment_date.required' => 'Investment date is required',
            'investment_round_id.required' => 'Please select an investment round',
        ]);

        // Get investment round for equity calculation
        $round = $this->roundRepository->findById($validated['investment_round_id']);
        
        // Auto-calculate equity percentage if not provided or is zero
        $equityPercentage = (float) ($validated['equity_percentage'] ?? 0);
        if ($equityPercentage == 0 && $round && $round->getValuation() > 0) {
            $equityPercentage = ((float) $validated['investment_amount'] / $round->getValuation()) * 100;
        }

        $account = InvestorAccount::create(
            userId: $validated['user_id'] ?? null,
            name: $validated['name'],
            email: $validated['email'],
            investmentAmount: (float) $validated['investment_amount'],
            investmentDate: new DateTimeImmutable($validated['investment_date']),
            investmentRoundId: $validated['investment_round_id'],
            equityPercentage: $equityPercentage
        );

        $savedAccount = $this->accountRepository->save($account);

        // Update investment round raised amount
        if ($round) {
            $round->addInvestment((float) $validated['investment_amount']);
            $this->roundRepository->save($round);
        }

        // Generate access code for investor portal
        $accessCode = $this->generateAccessCode($savedAccount->getEmail(), $savedAccount->getId());

        $message = "Investment recorded successfully. ";
        if (!$validated['user_id']) {
            $message .= "Investor portal access - Email: {$savedAccount->getEmail()}, Access Code: {$accessCode}";
        } else {
            $message .= "Linked to existing user account. Investor can use their regular login.";
        }

        return redirect()->route('admin.investor-accounts.index')
            ->with('success', $message);
    }

    public function edit(int $id)
    {
        $account = $this->accountRepository->findById($id);
        
        if (!$account) {
            return redirect()->route('admin.investor-accounts.index')
                ->with('error', 'Investor account not found');
        }

        $rounds = $this->roundRepository->all();
        
        $roundsData = array_map(function($round) {
            return [
                'id' => $round->getId(),
                'name' => $round->getName(),
            ];
        }, $rounds);

        return Inertia::render('Admin/Investor/Accounts/Edit', [
            'account' => [
                'id' => $account->getId(),
                'user_id' => $account->getUserId(),
                'name' => $account->getName(),
                'email' => $account->getEmail(),
                'investment_amount' => $account->getInvestmentAmount(),
                'investment_date' => $account->getInvestmentDate()->format('Y-m-d'),
                'investment_round_id' => $account->getInvestmentRoundId(),
                'status' => $account->getStatus()->value(),
                'equity_percentage' => $account->getEquityPercentage(),
            ],
            'rounds' => $roundsData,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $account = $this->accountRepository->findById($id);
        
        if (!$account) {
            return redirect()->route('admin.investor-accounts.index')
                ->with('error', 'Investor account not found');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'equity_percentage' => 'required|numeric|min:0|max:100',
        ]);

        // Note: We don't allow changing investment amount or round after creation
        // to maintain data integrity. Create a new record if needed.

        $account = InvestorAccount::fromPersistence(
            id: $account->getId(),
            userId: $account->getUserId(),
            name: $validated['name'],
            email: $validated['email'],
            investmentAmount: $account->getInvestmentAmount(),
            investmentDate: $account->getInvestmentDate(),
            investmentRoundId: $account->getInvestmentRoundId(),
            status: $account->getStatus(),
            equityPercentage: (float) $validated['equity_percentage'],
            createdAt: $account->getCreatedAt(),
            updatedAt: new DateTimeImmutable()
        );

        $this->accountRepository->save($account);

        return redirect()->route('admin.investor-accounts.index')
            ->with('success', 'Investor account updated successfully');
    }

    public function convertToShareholder(int $id)
    {
        $account = $this->accountRepository->findById($id);
        
        if (!$account) {
            return back()->with('error', 'Investor account not found');
        }

        $account->convertToShareholder();
        $this->accountRepository->save($account);

        return back()->with('success', 'Investor converted to shareholder');
    }

    public function markAsExited(int $id)
    {
        $account = $this->accountRepository->findById($id);
        
        if (!$account) {
            return back()->with('error', 'Investor account not found');
        }

        $account->exit();
        $this->accountRepository->save($account);

        return back()->with('success', 'Investor marked as exited');
    }

    public function destroy(int $id)
    {
        $this->accountRepository->delete($id);

        return redirect()->route('admin.investor-accounts.index')
            ->with('success', 'Investor account deleted successfully');
    }

    /**
     * Generate access code for investor portal
     */
    private function generateAccessCode(string $email, int $id): string
    {
        // Simple access code: first 4 chars of email + investor ID
        // In production, implement proper password system
        return strtoupper(substr($email, 0, 4)) . $id;
    }
}
