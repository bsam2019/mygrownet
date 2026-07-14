<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Comment;
use App\Domain\StockFlow\Repositories\CommentRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CommentId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCommentModel;
use DateTimeImmutable;

class EloquentCommentRepository implements CommentRepositoryInterface
{
    public function findById(CommentId $id): ?Comment
    {
        $model = SaCommentModel::with('user')->find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCommentable(CompanyId $companyId, string $type, int $id): array
    {
        return SaCommentModel::with('user')
            ->where('sa_company_id', $companyId->toInt())
            ->where('commentable_type', $type)
            ->where('commentable_id', $id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function save(Comment $comment): Comment
    {
        $data = [
            'sa_company_id' => $comment->getCompanyId()->toInt(),
            'commentable_type' => $comment->getCommentableType(),
            'commentable_id' => $comment->getCommentableId(),
            'sa_user_id' => $comment->getUserIdValue(),
            'body' => $comment->getBody(),
        ];

        if ($comment->id() === 0) {
            $model = SaCommentModel::create($data);
        } else {
            $model = SaCommentModel::find($comment->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(CommentId $id): void
    {
        SaCommentModel::destroy($id->toInt());
    }

    public function countByCommentable(CompanyId $companyId, string $type, int $id): int
    {
        return SaCommentModel::where('sa_company_id', $companyId->toInt())
            ->where('commentable_type', $type)
            ->where('commentable_id', $id)
            ->count();
    }

    private function toDomainEntity(SaCommentModel $model): Comment
    {
        return Comment::reconstitute(
            id: CommentId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            commentableType: $model->commentable_type,
            commentableId: (int) $model->commentable_id,
            userId: UserId::fromInt($model->sa_user_id),
            body: $model->body,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
