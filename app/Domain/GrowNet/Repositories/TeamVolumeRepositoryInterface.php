<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Repositories;

use App\Domain\GrowNet\Entities\TeamVolume;
use App\Domain\GrowNet\ValueObjects\MemberId;
use DateTimeImmutable;

interface TeamVolumeRepositoryInterface
{
    public function findCurrentByMemberId(MemberId $memberId): ?TeamVolume;

    /** @return TeamVolume[] */
    public function findHistoryByMemberId(MemberId $memberId, int $months = 6): array;

    public function findLatestByMemberId(MemberId $memberId): ?TeamVolume;

    public function sumByMemberIdAndMonth(MemberId $memberId, int $month, int $year): float;

    public function sumTeamVolumeByMemberId(MemberId $memberId): float;

    public function save(TeamVolume $teamVolume): TeamVolume;

    public function sumBySource(MemberId $memberId, string $source, int $month, int $year): float;
}
