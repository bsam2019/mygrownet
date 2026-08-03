<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Repositories\VideoRentalRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;

/**
 * Pay-per-view / video rental access.
 */
class RentalService
{
    private const DURATIONS = [
        '24_hours' => ['hours' => 24],
        '48_hours' => ['hours' => 48],
        '7_days' => ['days' => 7],
        '30_days' => ['days' => 30],
    ];

    public function __construct(
        private VideoRentalRepositoryInterface $rentalRepo,
        private VideoRepositoryInterface $videoRepo,
    ) {}

    public function rent(int $userId, int $videoId, float $price, ?string $providerReference = null, string $accessDuration = '48_hours'): array
    {
        $video = $this->videoRepo->findById($videoId);
        if ($video === null) {
            throw new \RuntimeException('Video not found');
        }

        $active = $this->rentalRepo->activeRental($userId, $videoId);
        if ($active !== null) {
            return $active->toArray();
        }

        [$intervalSpec] = $this->resolveDuration($accessDuration);

        $grantedAt = now();
        $rental = $this->rentalRepo->create([
            'user_id' => $userId,
            'video_id' => $videoId,
            'price' => $price,
            'currency' => 'ZMW',
            'access_duration' => $accessDuration,
            'granted_at' => $grantedAt,
            'expires_at' => $grantedAt->copy()->add($intervalSpec),
            'provider_reference' => $providerReference,
            'status' => 'active',
        ]);

        return $rental->toArray();
    }

    public function hasActiveRental(int $userId, int $videoId): bool
    {
        return $this->rentalRepo->hasActiveRental($userId, $videoId);
    }

    /**
     * @return array{\DateInterval, string}
     */
    protected function resolveDuration(string $accessDuration): array
    {
        if (! isset(self::DURATIONS[$accessDuration])) {
            throw new \InvalidArgumentException("Unsupported rental duration: {$accessDuration}");
        }

        $config = self::DURATIONS[$accessDuration];

        $spec = isset($config['days'])
            ? "P{$config['days']}D"
            : "PT{$config['hours']}H";

        return [new \DateInterval($spec), $accessDuration];
    }
}
