<?php

namespace App\Infrastructure\Persistence\Eloquent\Support;

use App\Domain\Support\Entities\TicketComment;
use App\Domain\Support\Repositories\TicketCommentRepository;
use App\Domain\Support\ValueObjects\TicketId;
use App\Domain\Support\ValueObjects\UserId;

class EloquentTicketCommentRepository implements TicketCommentRepository
{
    public function save(TicketComment $comment): void
    {
        TicketCommentModel::updateOrCreate(
            ['id' => $comment->id()],
            [
                'ticket_id' => $comment->ticketId()->value(),
                'user_id' => $comment->userId()->value(),
                'comment' => $comment->comment(),
                'is_internal' => $comment->isInternal(),
            ]
        );
    }

    public function findByTicketId(TicketId $ticketId, bool $includeInternal = false): array
    {
        $query = TicketCommentModel::where('ticket_id', $ticketId->value())
            ->orderBy('created_at', 'asc');

        if (!$includeInternal) {
            $query->where('is_internal', false);
        }

        return $query->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function nextId(): int
    {
        return (TicketCommentModel::max('id') ?? 0) + 1;
    }

    private function toDomainEntity(TicketCommentModel $model): TicketComment
    {
        $reflection = new \ReflectionClass(TicketComment::class);
        $comment = $reflection->newInstanceWithoutConstructor();

        $properties = [
            'id' => $model->id,
            'ticketId' => TicketId::fromInt($model->ticket_id),
            'userId' => UserId::fromInt($model->user_id),
            'comment' => $model->comment,
            'isInternal' => $model->is_internal,
            'createdAt' => new \DateTimeImmutable($model->created_at->toDateTimeString()),
        ];

        foreach ($properties as $name => $value) {
            $property = $reflection->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($comment, $value);
        }

        return $comment;
    }
}
