<?php

namespace App\Domain\VentureBuilder\Services;

use App\Domain\VentureBuilder\Repositories\ResolutionRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\VoteRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\ShareholderRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\VentureRepositoryInterface;
use App\Domain\VentureBuilder\Entities\Resolution;
use App\Domain\VentureBuilder\Entities\Vote;
use App\Domain\VentureBuilder\ValueObjects\ResolutionStatus;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VentureVoteService
{
    public function __construct(
        private readonly ResolutionRepositoryInterface $resolutionRepository,
        private readonly VoteRepositoryInterface $voteRepository,
        private readonly ShareholderRepositoryInterface $shareholderRepository,
        private readonly VentureRepositoryInterface $ventureRepository,
    ) {}

    public function createResolution(
        int $ventureId,
        string $title,
        string $description,
        string $type = 'ordinary',
        ?string $votingEndsAt = null,
        float $passThreshold = 50.0
    ): array {
        $venture = $this->ventureRepository->findById($ventureId);
        if (!$venture || !in_array($venture->status->value(), ['funded', 'active'])) {
            throw new \InvalidArgumentException('Resolutions can only be created for funded or active ventures.');
        }

        $resolution = new Resolution(
            ventureId: $ventureId,
            title: $title,
            description: $description,
            type: $type,
            status: ResolutionStatus::draft(),
            votingStartsAt: new \DateTimeImmutable(),
            votingEndsAt: $votingEndsAt
                ? new \DateTimeImmutable($votingEndsAt)
                : (new \DateTimeImmutable())->modify('+14 days'),
            passThresholdPercentage: $passThreshold,
            createdBy: Auth::id(),
        );

        $saved = $this->resolutionRepository->save($resolution);
        return $saved->toArray();
    }

    public function openVoting(int $resolutionId): array
    {
        $resolution = $this->resolutionRepository->findById($resolutionId);
        if (!$resolution || $resolution->status->value() !== 'draft') {
            throw new \InvalidArgumentException('Only draft resolutions can be opened for voting.');
        }

        $this->resolutionRepository->updateStatus($resolutionId, 'voting');

        $updated = $this->resolutionRepository->findById($resolutionId);
        return $updated ? $updated->toArray() : [];
    }

    public function castVote(
        int $userId,
        int $resolutionId,
        string $vote,
        ?string $comment = null
    ): array {
        $resolution = $this->resolutionRepository->findById($resolutionId);
        if (!$resolution || !$resolution->isOpenForVoting()) {
            throw new \InvalidArgumentException('This resolution is not open for voting.');
        }

        $shareholder = $this->shareholderRepository->findActiveByUserAndVenture($userId, $resolution->ventureId);
        if (!$shareholder) {
            throw new \InvalidArgumentException('You are not an active shareholder in this venture.');
        }

        $existingVote = $this->voteRepository->findByResolutionAndShareholder($resolutionId, $shareholder->id ?? 0);
        if ($existingVote) {
            throw new \InvalidArgumentException('You have already voted on this resolution.');
        }

        if (!in_array($vote, ['for', 'against', 'abstain'])) {
            throw new \InvalidArgumentException('Vote must be "for", "against", or "abstain".');
        }

        return DB::transaction(function () use ($resolutionId, $shareholder, $userId, $vote, $comment, $resolution) {
            $voteEntity = new Vote(
                resolutionId: $resolutionId,
                shareholderId: $shareholder->id ?? 0,
                userId: $userId,
                vote: $vote,
                equityAtVote: $shareholder->equityPercentage,
                comment: $comment,
            );

            $saved = $this->voteRepository->save($voteEntity);

            $column = match ($vote) {
                'for' => 'votes_for',
                'against' => 'votes_against',
                'abstain' => 'votes_abstain',
            };

            $this->resolutionRepository->incrementVote($resolutionId, $column, $shareholder->equityPercentage ?? 0);

            AuditLog::logEvent(
                'venture_vote_cast',
                "Vote#{$saved->id}",
                $userId,
                null,
                $saved->toArray(),
                null,
                null,
                [
                    'resolution_title' => $resolution->title,
                    'venture_id' => $resolution->ventureId,
                    'vote' => $vote,
                    'equity_percentage' => $shareholder->equityPercentage,
                ]
            );

            return $saved->toArray();
        });
    }

    public function tallyResults(int $resolutionId): array
    {
        $resolution = $this->resolutionRepository->findById($resolutionId);
        if (!$resolution || $resolution->status->value() !== 'voting') {
            throw new \InvalidArgumentException('Only resolutions in voting status can be tallied.');
        }

        $votesFor = $resolution->votesFor ?? 0;
        $votesAgainst = $resolution->votesAgainst ?? 0;
        $totalVoted = $resolution->totalVotedEquity ?? 0;

        $passed = $votesFor >= $votesAgainst && $votesFor > 0 && $totalVoted > 0;
        $totalVotes = $votesFor + $votesAgainst;
        $forPercentage = $totalVotes > 0 ? ($votesFor / $totalVotes) * 100 : 0;
        $passThreshold = $resolution->passThresholdPercentage ?? 50;
        $newStatus = $passed && $forPercentage >= $passThreshold ? 'passed' : 'rejected';

        $abstain = $resolution->votesAbstain ?? 0;
        $threshold = $resolution->passThresholdPercentage ?? 50;
        $resultNotes = "For: {$votesFor}%, Against: {$votesAgainst}%, " .
            "Abstain: {$abstain}%, Threshold: {$threshold}%. " .
            ($newStatus === 'passed' ? 'Resolution passed.' : 'Resolution rejected.');

        $this->resolutionRepository->updateStatus($resolutionId, $newStatus, [
            'result_notes' => $resultNotes,
        ]);

        AuditLog::logEvent(
            'venture_resolution_' . $newStatus,
            "Resolution#$resolutionId",
            null,
            ['status' => 'voting'],
            [],
            null,
            null,
            [
                'resolution_title' => $resolution->title,
                'venture_id' => $resolution->ventureId,
                'votes_for' => $votesFor,
                'votes_against' => $votesAgainst,
            ]
        );

        $updated = $this->resolutionRepository->findById($resolutionId);
        return $updated ? $updated->toArray() : [];
    }
}
