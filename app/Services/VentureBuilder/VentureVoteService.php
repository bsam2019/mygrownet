<?php

namespace App\Services\VentureBuilder;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureResolutionModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureVoteModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareholderModel;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VentureVoteService
{
    public function createResolution(
        VentureModel $venture,
        string $title,
        string $description,
        string $type = 'ordinary',
        ?string $votingEndsAt = null,
        float $passThreshold = 50.0
    ): VentureResolutionModel {
        if (!in_array($venture->status, ['funded', 'active'])) {
            throw new \InvalidArgumentException('Resolutions can only be created for funded or active ventures.');
        }

        return VentureResolutionModel::create([
            'venture_id' => $venture->id,
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'status' => 'draft',
            'voting_starts_at' => now(),
            'voting_ends_at' => $votingEndsAt ? now()->parse($votingEndsAt) : now()->addDays(14),
            'pass_threshold_percentage' => $passThreshold,
            'created_by' => Auth::id(),
        ]);
    }

    public function openVoting(VentureResolutionModel $resolution): VentureResolutionModel
    {
        if ($resolution->status !== 'draft') {
            throw new \InvalidArgumentException('Only draft resolutions can be opened for voting.');
        }

        $resolution->update(['status' => 'voting', 'voting_starts_at' => now()]);

        return $resolution->fresh();
    }

    public function castVote(
        User $user,
        VentureResolutionModel $resolution,
        string $vote,
        ?string $comment = null
    ): VentureVoteModel {
        if (!$resolution->isOpenForVoting()) {
            throw new \InvalidArgumentException('This resolution is not open for voting.');
        }

        $shareholder = VentureShareholderModel::where('venture_id', $resolution->venture_id)
            ->where('user_id', $user->id)
            ->active()
            ->first();

        if (!$shareholder) {
            throw new \InvalidArgumentException('You are not an active shareholder in this venture.');
        }

        $existingVote = VentureVoteModel::where('resolution_id', $resolution->id)
            ->where('shareholder_id', $shareholder->id)
            ->first();

        if ($existingVote) {
            throw new \InvalidArgumentException('You have already voted on this resolution.');
        }

        if (!in_array($vote, ['for', 'against', 'abstain'])) {
            throw new \InvalidArgumentException('Vote must be "for", "against", or "abstain".');
        }

        DB::beginTransaction();
        try {
            $voteRecord = VentureVoteModel::create([
                'resolution_id' => $resolution->id,
                'shareholder_id' => $shareholder->id,
                'user_id' => $user->id,
                'vote' => $vote,
                'equity_at_vote' => $shareholder->equity_percentage,
                'comment' => $comment,
                'voted_at' => now(),
            ]);

            $column = match ($vote) {
                'for' => 'votes_for',
                'against' => 'votes_against',
                'abstain' => 'votes_abstain',
            };

            $resolution->increment($column, $shareholder->equity_percentage);
            $resolution->increment('total_voted_equity', $shareholder->equity_percentage);

            AuditLog::logEvent(
                'venture_vote_cast',
                $voteRecord,
                $user->id,
                null,
                $voteRecord->toArray(),
                null,
                null,
                [
                    'resolution_title' => $resolution->title,
                    'venture_id' => $resolution->venture_id,
                    'vote' => $vote,
                    'equity_percentage' => $shareholder->equity_percentage,
                ]
            );

            DB::commit();

            return $voteRecord;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function tallyResults(VentureResolutionModel $resolution): VentureResolutionModel
    {
        if ($resolution->status !== 'voting') {
            throw new \InvalidArgumentException('Only resolutions in voting status can be tallied.');
        }

        $passed = $resolution->votes_for >= $resolution->votes_against
            && $resolution->votes_for > 0
            && $resolution->total_voted_equity > 0;

        $totalVotes = $resolution->votes_for + $resolution->votes_against;
        $forPercentage = $totalVotes > 0 ? ($resolution->votes_for / $totalVotes) * 100 : 0;

        $newStatus = $passed && $forPercentage >= $resolution->pass_threshold_percentage ? 'passed' : 'rejected';

        $resolution->update([
            'status' => $newStatus,
            'result_notes' => "For: {$resolution->votes_for}%, Against: {$resolution->votes_against}%, " .
                "Abstain: {$resolution->votes_abstain}%, Threshold: {$resolution->pass_threshold_percentage}%. " .
                ($newStatus === 'passed' ? 'Resolution passed.' : 'Resolution rejected.'),
        ]);

        AuditLog::logEvent(
            'venture_resolution_' . $newStatus,
            $resolution,
            null,
            ['status' => 'voting'],
            $resolution->toArray(),
            null,
            null,
            [
                'resolution_title' => $resolution->title,
                'venture_id' => $resolution->venture_id,
                'votes_for' => $resolution->votes_for,
                'votes_against' => $resolution->votes_against,
            ]
        );

        return $resolution->fresh();
    }
}
