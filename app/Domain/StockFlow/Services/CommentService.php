<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\Comment;
use App\Domain\StockFlow\Exceptions\CommentNotFoundException;
use App\Domain\StockFlow\Repositories\CommentRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CommentId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\UserId;

class CommentService
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
    ) {}

    public function getComments(int $companyId, string $type, int $id): array
    {
        $comments = $this->commentRepository->findByCommentable(
            CompanyId::fromInt($companyId),
            $type,
            $id,
        );

        return array_map(fn(Comment $c) => $c->toArray(), $comments);
    }

    public function addComment(int $companyId, string $type, int $id, int $userId, string $body): Comment
    {
        $comment = Comment::create(
            companyId: CompanyId::fromInt($companyId),
            commentableType: $type,
            commentableId: $id,
            userId: UserId::fromInt($userId),
            body: $body,
        );

        return $this->commentRepository->save($comment);
    }

    public function updateComment(int $commentId, string $body): Comment
    {
        $comment = $this->commentRepository->findById(CommentId::fromInt($commentId));

        if (!$comment) {
            throw new CommentNotFoundException($commentId);
        }

        $comment->updateBody($body);
        return $this->commentRepository->save($comment);
    }

    public function deleteComment(int $commentId): void
    {
        $comment = $this->commentRepository->findById(CommentId::fromInt($commentId));

        if (!$comment) {
            throw new CommentNotFoundException($commentId);
        }

        $this->commentRepository->delete(CommentId::fromInt($commentId));
    }

    public function getCommentCount(int $companyId, string $type, int $id): int
    {
        return $this->commentRepository->countByCommentable(
            CompanyId::fromInt($companyId),
            $type,
            $id,
        );
    }
}
