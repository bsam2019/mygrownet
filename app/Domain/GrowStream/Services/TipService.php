<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Repositories\CreatorTipRepositoryInterface;

class TipService
{
    public function __construct(
        private CreatorTipRepositoryInterface $tipRepo,
    ) {}

    public function send(int $userId, int $creatorId, float $amount, ?string $message = null, bool $anonymous = false, ?string $providerReference = null): array
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Tip amount must be greater than zero');
        }

        $tip = $this->tipRepo->create([
            'user_id' => $userId,
            'creator_id' => $creatorId,
            'amount' => $amount,
            'currency' => 'ZMW',
            'message' => $message,
            'is_anonymous' => $anonymous,
            'provider_reference' => $providerReference,
            'status' => 'completed',
        ]);

        return $tip->toArray();
    }

    public function totalForCreator(int $creatorId): float
    {
        return $this->tipRepo->totalForCreator($creatorId);
    }

    public function countForCreator(int $creatorId): int
    {
        return $this->tipRepo->countForCreator($creatorId);
    }
}
