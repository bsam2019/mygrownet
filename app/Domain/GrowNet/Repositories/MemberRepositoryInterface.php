<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Repositories;

use App\Domain\GrowNet\Entities\Member;
use App\Domain\GrowNet\ValueObjects\MemberId;

interface MemberRepositoryInterface
{
    public function findById(MemberId $id): ?Member;
    public function findByUserId(int $userId): ?Member;
    public function findByReferralCode(string $code): ?Member;
    public function save(Member $member): Member;
    public function delete(MemberId $id): void;

    /** @return Member[] */
    public function findEligibleForTierUpgrade(): array;

    /** @return Member[] */
    public function findWithExpiringSubscriptions(\DateTimeImmutable $cutoff): array;

    public function countByTier(string $tier): int;

    /** @return array{member: Member, team_volume: float, referral_count: int}[] */
    public function getLeaderboard(int $limit = 10): array;
}
