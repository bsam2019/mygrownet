<?php

declare(strict_types=1);

namespace App\Domain\LifePlus\Repositories;

use App\Domain\LifePlus\Entities\LifePlusCommunityPost;

interface CommunityPostRepositoryInterface
{
    public function findById(int $id): ?LifePlusCommunityPost;

    public function save(LifePlusCommunityPost $post): LifePlusCommunityPost;

    public function delete(int $id): bool;

    public function findActive(array $filters = []): array;
}
