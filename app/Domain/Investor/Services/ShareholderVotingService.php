<?php

namespace App\Domain\Investor\Services;

use App\Models\ShareholderResolution;
use App\Models\ShareholderVote;
use App\Models\ProxyDelegation;
use App\Models\InvestorAccount;
use Illuminate\Support\Collection;

class ShareholderVotingService
{
    public function getActiveResolutions(): Collection
    {
        return ShareholderResolution::where('status', 'active')
            ->where('voting_start', '<=', now())
            ->where('voting_end', '>=', now())
            ->orderBy('voting_end', 'asc')
            ->get();
    }

    public function getUpcomingResolutions(): Collection
    {
        return ShareholderResolution::where('status', 'active')
            ->where('voting_start', '>', now())
            ->orderBy('voting_start', 'asc')
            ->get();
    }

    public function getPastResolutions(int $limit = 10): Collection
    {
        return ShareholderResolution::whereIn('status', ['closed', 'passed', 'failed'])
            ->orderBy('voting_end', 'desc')
            ->limit($limit)
            ->get();
    }

    public function castVote(
        int $resolutionId,
        int $investorAccountId,
        string $vote,
        ?string $selectedOption = null
    ): ShareholderVote {
        $resolution = ShareholderResolution::findOrFail($resolutionId);
        $investor = InvestorAccount::findOrFail($investorAccountId);

        if (!$resolution->isActive()) {
            throw new \Exception('Voting is not currently open for this resolution.');
        }

        $existingVote = ShareholderVote::where('resolution_id', $resolutionId)
            ->where('investor_account_id', $investorAccountId)
            ->first();

        if ($existingVote) {
            throw new \Exception('You have already voted on this resolution.');
        }

        return ShareholderVote::create([
            'resolution_id' => $resolutionId,
            'investor_account_id' => $investorAccountId,
            'vote' => $vote,
            'voting_power' => $investor->equity_percentage ?? 0,
            'selected_option' => $selectedOption,
            'ip_address' => request()->ip(),
            'voted_at' => now(),
        ]);
    }

    public function getInvestorVoteHistory(int $investorAccountId): Collection
    {
        return ShareholderVote::with('resolution')
            ->where('investor_account_id', $investorAccountId)
            ->orderBy('voted_at', 'desc')
            ->get();
    }

    public function hasVoted(int $resolutionId, int $investorAccountId): bool
    {
        return ShareholderVote::where('resolution_id', $resolutionId)
            ->where('investor_account_id', $investorAccountId)
            ->exists();
    }

    public function delegateProxy(
        int $delegatorId,
        int $delegateId,
        ?int $resolutionId = null,
        ?string $validUntil = null,
        ?string $instructions = null
    ): ProxyDelegation {
        if ($delegatorId === $delegateId) {
            throw new \Exception('Cannot delegate proxy to yourself.');
        }

        return ProxyDelegation::create([
            'delegator_id' => $delegatorId,
            'delegate_id' => $delegateId,
            'resolution_id' => $resolutionId,
            'is_general' => $resolutionId === null,
            'valid_from' => now(),
            'valid_until' => $validUntil,
            'status' => 'active',
            'instructions' => $instructions,
        ]);
    }

    public function revokeProxy(int $delegationId, int $delegatorId): bool
    {
        $delegation = ProxyDelegation::where('id', $delegationId)
            ->where('delegator_id', $delegatorId)
            ->firstOrFail();

        return $delegation->update(['status' => 'revoked']);
    }

    public function getResolutionResults(int $resolutionId): array
    {
        $resolution = ShareholderResolution::with('votes')->findOrFail($resolutionId);
        return $resolution->getVoteResults();
    }
}
