<?php

namespace App\Application\Library\UseCases;

use App\Domain\Library\Repositories\LibraryResourceRepositoryInterface;
use App\Models\User;
use App\Models\LibraryResourceAccess;

class ViewResourceUseCase
{
    public function __construct(
        private readonly LibraryResourceRepositoryInterface $repository
    ) {}

    public function execute(User $user, int $resourceId): void
    {
        $resource = $this->repository->findById($resourceId);
        
        if (!$resource) {
            throw new \InvalidArgumentException('Resource not found');
        }

        // Track access
        LibraryResourceAccess::create([
            'user_id' => $user->id,
            'library_resource_id' => $resourceId,
            'accessed_at' => now(),
        ]);

        // Increment view count
        $resource->incrementViewCount();
        $this->repository->save($resource);
    }
}
