<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowStream;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorAgreement;
use App\Domain\GrowStream\Repositories\CreatorAgreementRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class EloquentCreatorAgreementRepository implements CreatorAgreementRepositoryInterface
{
    public function recordAcceptance(
        int $creatorProfileId,
        string $version,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): CreatorAgreement {
        return CreatorAgreement::create([
            'creator_profile_id' => $creatorProfileId,
            'version' => $version,
            'accepted' => true,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'accepted_at' => now(),
        ]);
    }

    public function latestAccepted(int $creatorProfileId): ?CreatorAgreement
    {
        return CreatorAgreement::where('creator_profile_id', $creatorProfileId)
            ->where('accepted', true)
            ->latest('accepted_at')
            ->first();
    }

    public function hasAccepted(int $creatorProfileId, string $version): bool
    {
        return CreatorAgreement::where('creator_profile_id', $creatorProfileId)
            ->where('version', $version)
            ->where('accepted', true)
            ->exists();
    }

    public function query(): Builder
    {
        return CreatorAgreement::query();
    }
}
