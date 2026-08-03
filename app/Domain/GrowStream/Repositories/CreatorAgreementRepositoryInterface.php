<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Repositories;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorAgreement;
use Illuminate\Database\Eloquent\Builder;

interface CreatorAgreementRepositoryInterface
{
    public function recordAcceptance(
        int $creatorProfileId,
        string $version,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): CreatorAgreement;

    public function latestAccepted(int $creatorProfileId): ?CreatorAgreement;

    public function hasAccepted(int $creatorProfileId, string $version): bool;

    public function query(): Builder;
}
