<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Community\Repositories\VotingRepository;
use App\Domain\Community\ValueObjects\ProjectId;
use App\Domain\Community\ValueObjects\VotingPower;
use App\Domain\MLM\ValueObjects\UserId;
use App\Models\ProjectVote;
use DateTimeImmutable;

class EloquentVotingRepository implements VotingRepository
{
    public function findById(int $id): ?array
    {
        $vote = ProjectVote::with(['user', 'project'])->find($id);
        return $vote ? $this->toVoteArray($vote) : null;
    }

    public function findByProjectId(ProjectId $projectId): array
    {
        $votes = ProjectVote::forProject($projectId->value())
            ->with(['user', 'project'])
            ->orderBy('voted_at', 'desc')
            ->get();

        return $votes->map(fn($v) => $this->toVoteArray($v))->toArray();
    }

    public function findByUserId(UserId $userId): array
    {
        $votes = ProjectVote::where('user_id', $userId->value())
            ->with(['user', 'project'])
            ->orderBy('voted_at', 'desc')
            ->get();

        return $votes->map(fn($v) => $this->toVoteArray($v))->toArray();
    }

    public function findBySessionId(string $sessionId): array
    {
        $votes = ProjectVote::forSession($sessionId)
            ->with(['user', 'project'])
            ->orderBy('voted_at', 'desc')
            ->get();

        return $votes->map(fn($v) => $this->toVoteArray($v))->toArray();
    }

    public function findActiveVotingSessions(): array
    {
        $votes = ProjectVote::active()
            ->selectRaw('vote_session_id, MIN(voted_at) as session_start, vote_deadline, vote_type, vote_subject')
            ->groupBy('vote_session_id', 'vote_deadline', 'vote_type', 'vote_subject')
            ->orderBy('vote_deadline')
            ->get();

        return $votes->map(function ($vote) {
            return [
                'session_id' => $vote->vote_session_id,
                'vote_type' => $vote->vote_type,
                'vote_subject' => $vote->vote_subject,
                'session_start' => $vote->session_start,
                'vote_deadline' => $vote->vote_deadline,
                'is_active' => true,
            ];
        })->toArray();
    }

    public function findCompletedVotingSessions(): array
    {
        $votes = ProjectVote::completed()
            ->selectRaw('vote_session_id, MIN(voted_at) as session_start, vote_deadline, vote_type, vote_subject')
            ->groupBy('vote_session_id', 'vote_deadline', 'vote_type', 'vote_subject')
            ->orderBy('vote_deadline', 'desc')
            ->get();

        return $votes->map(function ($vote) {
            return [
                'session_id' => $vote->vote_session_id,
                'vote_type' => $vote->vote_type,
                'vote_subject' => $vote->vote_subject,
                'session_start' => $vote->session_start,
                'vote_deadline' => $vote->vote_deadline,
                'is_active' => false,
            ];
        })->toArray();
    }

    public function hasUserVotedInSession(UserId $userId, string $sessionId): bool
    {
        return ProjectVote::where('user_id', $userId->value())
            ->where('vote_session_id', $sessionId)
            ->exists();
    }

    public function getVotingSessionResults(string $sessionId): array
    {
        return ProjectVote::getVoteSessionResults($sessionId);
    }

    public function calculateTotalVotingPower(string $sessionId): VotingPower
    {
        $totalPower = ProjectVote::forSession($sessionId)->sum('voting_power');
        return VotingPower::fromFloat($totalPower);
    }

    public function findVotesByType(string $voteType): array
    {
        $votes = ProjectVote::byType($voteType)
            ->with(['user', 'project'])
            ->orderBy('voted_at', 'desc')
            ->get();

        return $votes->map(fn($v) => $this->toVoteArray($v))->toArray();
    }

    public function getUserVotingHistory(UserId $userId): array
    {
        $votes = ProjectVote::where('user_id', $userId->value())
            ->with(['project'])
            ->orderBy('voted_at', 'desc')
            ->get();

        return $votes->map(fn($v) => $this->toVoteArray($v))->toArray();
    }

    public function findVotesRequiringAttention(int $days = 3): array
    {
        $votes = ProjectVote::active()
            ->where('vote_deadline', '<=', now()->addDays($days))
            ->selectRaw('vote_session_id, vote_type, vote_subject, vote_deadline, COUNT(*) as vote_count')
            ->groupBy('vote_session_id', 'vote_type', 'vote_subject', 'vote_deadline')
            ->orderBy('vote_deadline')
            ->get();

        return $votes->toArray();
    }

    public function getVotingStatsByProject(ProjectId $projectId): array
    {
        $stats = ProjectVote::forProject($projectId->value())
            ->selectRaw('
                vote_type,
                COUNT(*) as vote_count,
                SUM(voting_power) as total_voting_power,
                AVG(voting_power) as avg_voting_power
            ')
            ->groupBy('vote_type')
            ->get();

        return $stats->mapWithKeys(function ($stat) {
            return [$stat->vote_type => [
                'vote_count' => $stat->vote_count,
                'total_voting_power' => (float) $stat->total_voting_power,
                'avg_voting_power' => (float) $stat->avg_voting_power,
            ]];
        })->toArray();
    }

    public function getVotingParticipationByTier(): array
    {
        $stats = ProjectVote::selectRaw('
            tier_at_vote,
            COUNT(*) as vote_count,
            SUM(voting_power) as total_voting_power,
            COUNT(DISTINCT user_id) as unique_voters
        ')
        ->groupBy('tier_at_vote')
        ->get();

        return $stats->mapWithKeys(function ($stat) {
            return [$stat->tier_at_vote => [
                'vote_count' => $stat->vote_count,
                'total_voting_power' => (float) $stat->total_voting_power,
                'unique_voters' => $stat->unique_voters,
            ]];
        })->toArray();
    }

    public function findMostInfluentialVoters(int $limit = 10): array
    {
        $voters = ProjectVote::selectRaw('
            user_id,
            COUNT(*) as vote_count,
            SUM(voting_power) as total_voting_power,
            AVG(voting_power) as avg_voting_power
        ')
        ->with('user')
        ->groupBy('user_id')
        ->orderBy('total_voting_power', 'desc')
        ->limit($limit)
        ->get();

        return $voters->map(function ($voter) {
            return [
                'user_id' => $voter->user_id,
                'user' => $voter->user,
                'vote_count' => $voter->vote_count,
                'total_voting_power' => (float) $voter->total_voting_power,
                'avg_voting_power' => (float) $voter->avg_voting_power,
            ];
        })->toArray();
    }

    public function getVotingSessionSummary(string $sessionId): array
    {
        return ProjectVote::getVoteSessionResults($sessionId);
    }

    public function createVotingSession(array $sessionData): string
    {
        return ProjectVote::createVoteSession(
            $sessionData['project'],
            $sessionData['vote_type'],
            $sessionData['subject'],
            $sessionData['description'] ?? null,
            $sessionData['options'] ?? null,
            $sessionData['duration_days'] ?? 7
        );
    }

    public function castVote(array $voteData): int
    {
        $vote = ProjectVote::castVote(
            $voteData['user'],
            $voteData['project'],
            $voteData['session_id'],
            $voteData['vote'],
            $voteData['comments'] ?? null
        );

        return $vote->id;
    }

    public function updateVote(int $voteId, array $voteData): void
    {
        ProjectVote::where('id', $voteId)->update($voteData);
    }

    public function deleteVote(int $voteId): void
    {
        ProjectVote::where('id', $voteId)->delete();
    }

    public function closeVotingSession(string $sessionId): void
    {
        ProjectVote::forSession($sessionId)
            ->update(['vote_deadline' => now()->subDay()]);
    }

    private function toVoteArray(ProjectVote $vote): array
    {
        return [
            'id' => $vote->id,
            'user_id' => $vote->user_id,
            'community_project_id' => $vote->community_project_id,
            'vote_type' => $vote->vote_type,
            'vote_subject' => $vote->vote_subject,
            'vote_description' => $vote->vote_description,
            'vote' => $vote->vote,
            'voting_power' => $vote->voting_power,
            'tier_at_vote' => $vote->tier_at_vote,
            'contribution_amount' => $vote->contribution_amount,
            'vote_options' => $vote->vote_options,
            'voter_comments' => $vote->voter_comments,
            'voted_at' => $vote->voted_at,
            'vote_session_id' => $vote->vote_session_id,
            'vote_deadline' => $vote->vote_deadline,
            'is_final_vote' => $vote->is_final_vote,
            'user' => $vote->user,
            'project' => $vote->project,
        ];
    }
}