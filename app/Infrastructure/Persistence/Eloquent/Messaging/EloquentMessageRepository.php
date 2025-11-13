<?php

namespace App\Infrastructure\Persistence\Eloquent\Messaging;

use App\Domain\Messaging\Entities\Message;
use App\Domain\Messaging\Repositories\MessageRepository;
use App\Domain\Messaging\ValueObjects\MessageContent;
use App\Domain\Messaging\ValueObjects\MessageId;
use App\Domain\Messaging\ValueObjects\UserId;
use DateTimeImmutable;

class EloquentMessageRepository implements MessageRepository
{
    public function create(
        UserId $senderId,
        UserId $recipientId,
        MessageContent $content,
        ?MessageId $parentId = null
    ): Message {
        $model = new MessageModel();
        $model->sender_id = $senderId->value();
        $model->recipient_id = $recipientId->value();
        $model->subject = $content->subject();
        $model->body = $content->body();
        $model->is_read = false;
        $model->read_at = null;
        $model->parent_id = $parentId?->value();
        $model->save();

        return $this->toDomainEntity($model);
    }

    public function save(Message $message): void
    {
        $model = MessageModel::find($message->id()->value());

        if (!$model) {
            throw new \DomainException('Message not found');
        }

        $model->sender_id = $message->senderId()->value();
        $model->recipient_id = $message->recipientId()->value();
        $model->subject = $message->content()->subject();
        $model->body = $message->content()->body();
        $model->is_read = $message->isRead();
        $model->read_at = $message->readAt();
        $model->parent_id = $message->parentId()?->value();

        $model->save();
    }

    public function findById(MessageId $id): ?Message
    {
        $model = MessageModel::find($id->value());

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByRecipient(UserId $recipientId, int $limit = 50, int $offset = 0): array
    {
        $models = MessageModel::where('recipient_id', $recipientId->value())
            ->whereNull('parent_id') // Only root messages
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findBySender(UserId $senderId, int $limit = 50, int $offset = 0): array
    {
        $models = MessageModel::where('sender_id', $senderId->value())
            ->whereNull('parent_id') // Only root messages
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findConversation(UserId $user1, UserId $user2, int $limit = 50): array
    {
        $models = MessageModel::where(function ($query) use ($user1, $user2) {
            $query->where('sender_id', $user1->value())
                ->where('recipient_id', $user2->value());
        })->orWhere(function ($query) use ($user1, $user2) {
            $query->where('sender_id', $user2->value())
                ->where('recipient_id', $user1->value());
        })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findReplies(MessageId $parentId): array
    {
        $models = MessageModel::where('parent_id', $parentId->value())
            ->orderBy('created_at', 'asc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function countUnreadByRecipient(UserId $recipientId): int
    {
        return MessageModel::where('recipient_id', $recipientId->value())
            ->where('is_read', false)
            ->count();
    }

    public function markAsRead(MessageId $id): void
    {
        MessageModel::where('id', $id->value())
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function delete(MessageId $id): void
    {
        MessageModel::destroy($id->value());
    }

    private function toDomainEntity(MessageModel $model): Message
    {
        return Message::reconstitute(
            id: MessageId::fromInt($model->id),
            senderId: UserId::fromInt($model->sender_id),
            recipientId: UserId::fromInt($model->recipient_id),
            content: MessageContent::create($model->subject, $model->body),
            isRead: $model->is_read,
            readAt: $model->read_at ? DateTimeImmutable::createFromMutable($model->read_at) : null,
            parentId: $model->parent_id ? MessageId::fromInt($model->parent_id) : null,
            createdAt: DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: DateTimeImmutable::createFromMutable($model->updated_at)
        );
    }
}
