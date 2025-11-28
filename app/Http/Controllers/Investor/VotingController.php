<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Domain\Investor\Services\ShareholderVotingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VotingController extends Controller
{
    public function __construct(
        private ShareholderVotingService $votingService
    ) {}

    public function index(Request $request)
    {
        $investorId = session('investor_id');
        
        if (!$investorId) {
            return redirect()->route('investor.login');
        }

        $investorAccount = \App\Models\InvestorAccount::find($investorId);
        
        if (!$investorAccount) {
            abort(403, 'Investor account not found.');
        }

        $activeResolutions = $this->votingService->getActiveResolutions();
        $upcomingResolutions = $this->votingService->getUpcomingResolutions();
        $pastResolutions = $this->votingService->getPastResolutions();
        $voteHistory = $this->votingService->getInvestorVoteHistory($investorAccount->id);

        // Add voting status to active resolutions
        $activeResolutions = $activeResolutions->map(function ($resolution) use ($investorAccount) {
            $resolution->has_voted = $this->votingService->hasVoted($resolution->id, $investorAccount->id);
            $resolution->results = $resolution->getVoteResults();
            return $resolution;
        });

        return Inertia::render('Investor/Voting', [
            'investor' => [
                'id' => $investorAccount->id,
                'name' => $investorAccount->name,
                'email' => $investorAccount->email,
            ],
            'activeResolutions' => $activeResolutions,
            'upcomingResolutions' => $upcomingResolutions,
            'pastResolutions' => $pastResolutions->map(fn($r) => [
                ...$r->toArray(),
                'results' => $r->getVoteResults(),
            ]),
            'voteHistory' => $voteHistory,
            'votingPower' => $investorAccount->equity_percentage ?? 0,
            'activePage' => 'voting',
            'unreadMessages' => 0,
            'unreadAnnouncements' => 0,
        ]);
    }

    public function castVote(Request $request)
    {
        $request->validate([
            'resolution_id' => 'required|exists:shareholder_resolutions,id',
            'vote' => 'required|in:for,against,abstain',
            'selected_option' => 'nullable|string',
        ]);

        $investorId = session('investor_id');
        
        if (!$investorId) {
            return redirect()->route('investor.login');
        }

        $investorAccount = \App\Models\InvestorAccount::find($investorId);

        if (!$investorAccount) {
            return back()->withErrors(['error' => 'Investor account not found.']);
        }

        try {
            $this->votingService->castVote(
                $request->resolution_id,
                $investorAccount->id,
                $request->vote,
                $request->selected_option
            );

            return back()->with('success', 'Your vote has been recorded.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function delegateProxy(Request $request)
    {
        $request->validate([
            'delegate_id' => 'required|exists:investor_accounts,id',
            'resolution_id' => 'nullable|exists:shareholder_resolutions,id',
            'valid_until' => 'nullable|date|after:today',
            'instructions' => 'nullable|string|max:1000',
        ]);

        $investorId = session('investor_id');
        
        if (!$investorId) {
            return redirect()->route('investor.login');
        }

        $investorAccount = \App\Models\InvestorAccount::find($investorId);

        if (!$investorAccount) {
            return back()->withErrors(['error' => 'Investor account not found.']);
        }

        try {
            $this->votingService->delegateProxy(
                $investorAccount->id,
                $request->delegate_id,
                $request->resolution_id,
                $request->valid_until,
                $request->instructions
            );

            return back()->with('success', 'Proxy delegation created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function revokeProxy(Request $request, int $delegationId)
    {
        $investorId = session('investor_id');
        
        if (!$investorId) {
            return redirect()->route('investor.login');
        }

        $investorAccount = \App\Models\InvestorAccount::find($investorId);

        if (!$investorAccount) {
            return back()->withErrors(['error' => 'Investor account not found.']);
        }

        try {
            $this->votingService->revokeProxy($delegationId, $investorAccount->id);
            return back()->with('success', 'Proxy delegation revoked.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function getResults(int $resolutionId)
    {
        $results = $this->votingService->getResolutionResults($resolutionId);
        return response()->json($results);
    }
}
