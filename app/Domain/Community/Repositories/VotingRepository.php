<?php

declare(strict_types=1);

namespace App\Domain\Community\Repositories;

use App\Domain\Community\ValueObjects\ProjectId;
use App\Domain\Community\ValueObjects\VotingPower;
use App\Domain\MLM\ValueObjects\UserId;
use DateTimeImmutable;

interface VotingRepository
{
    /**
     * Find vote by ID
     */
    public function findById(int $id): ?array;

    /**
     * Find votes by project
     */
    public function findByProjectId(ProjectId $projectId): array;

    /**
     * Find votes by user
     */
    public function findByUserId(UserId $userId): array;

    /**
     * Find votes by session ID
     */
    public function findBySessionId(string $sessionId): array;

    /**
     * Find active voting sessions
     */
    public function findActiveVotingSessions(): array;

    /**
     * Find completed voting sessions
     */
    public function findCompletedVotingSessions(): array;

    /**
     * Check if user has voted in session
     */
    public function hasUserVotedInSession(UserId $userId, string $sessionId): bool;

    /**
     * Get voting session results
     */
    public function getVotingSessionResults(string $sessionId): array;

    /**
     * Calculate total voting power for session
     */
    public function calculateTotalVotingPower(string $sessionId): VotingPower;

    /**
     * Find votes by type
     */
    public function findVotesByType(string $voteType): array;

    /**
     * Get user's voting history
     */
    public function getUserVotingHistory(UserId $userId): array;

    /**
     * Find votes requiring attention (deadlines approaching)
     */
    public function findVotesRequiringAttention(int $days = 3): array;

    /**
     * Get voting statistics by project
     */
    public function getVotingStatsByProject(ProjectId $projectId): array;

    /**
     * Get voting participation rates by tier
     */
    public function getVotingParticipationByTier(): array;

    /**
     * Find most influential voters
     */
    public function findMostInfluentialVoters(int $limit = 10): array;

    /**
     * Get voting session summary
     */
    public function getVotingSessionSummary(string $sessionId): array;

    /**
     * Create voting session
     */
    public function createVotingSession(array $sessionData): string;

    /**
     * Cast vote
     */
    public function castVote(array $voteData): int;

    /**
     * Update vote
     */
    public function updateVote(int $voteId, array $voteData): void;

    /**
     * Delete vote
     */
    public function deleteVote(int $voteId): void;

    /**
     * Close voting session
     */
    public function closeVotingSession(string $sessionId): void;
}