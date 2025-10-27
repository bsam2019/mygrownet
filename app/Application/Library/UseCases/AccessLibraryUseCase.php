<?php

namespace App\Application\Library\UseCases;

use App\Domain\Library\Policies\LibraryAccessPolicy;
use App\Domain\Library\Repositories\LibraryResourceRepositoryInterface;
use App\Domain\Library\ValueObjects\AccessPeriod;
use App\Models\User;
use DateTimeImmutable;

class AccessLibraryUseCase
{
    public function __construct(
        private readonly LibraryResourceRepositoryInterface $repository,
        private readonly LibraryAccessPolicy $accessPolicy
    ) {}

    public function execute(User $user): array
    {
        // Build access period from user data
        $freeAccessPeriod = $user->library_access_until 
            ? AccessPeriod::fromDates(
                $user->starter_kit_purchased_at ?? new DateTimeImmutable(),
                DateTimeImmutable::createFromMutable($user->library_access_until)
              )
            : null;

        // Check access
        $hasAccess = $this->accessPolicy->canAccessLibrary(
            $user->has_starter_kit,
            $freeAccessPeriod,
            $user->hasActiveSubscription()
        );

        if (!$hasAccess) {
            return [
                'hasAccess' => false,
                'reason' => $this->accessPolicy->getAccessDenialReason(
                    $user->has_starter_kit,
                    $freeAccessPeriod,
                    $user->hasActiveSubscription()
                ),
            ];
        }

        // Get resources
        $resources = $this->repository->findActive();
        $featured = $this->repository->findFeatured();

        return [
            'hasAccess' => true,
            'resources' => $resources,
            'featured' => $featured,
            'freeAccessDaysRemaining' => $freeAccessPeriod?->daysRemaining(),
        ];
    }
}
