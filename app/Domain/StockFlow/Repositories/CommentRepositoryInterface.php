<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Comment;
use App\Domain\StockFlow\ValueObjects\CommentId;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface CommentRepositoryInterface
{
    public function findById(CommentId $id): ?Comment;
    public function findByCommentable(CompanyId $companyId, string $type, int $id): array;
    public function save(Comment $comment): Comment;
    public function delete(CommentId $id): void;
    public function countByCommentable(CompanyId $companyId, string $type, int $id): int;
}
