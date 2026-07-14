<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Message;
use App\Domain\StockFlow\Repositories\MessageRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\MessageId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaMessageModel;
use DateTimeImmutable;

class EloquentMessageRepository implements MessageRepositoryInterface
{
    public function findById(MessageId $id): ?Message
    {
        $model = SaMessageModel::with(['sender', 'recipient'])->find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySender(CompanyId $companyId, UserId $senderId): array
    {
        return SaMessageModel::with(['sender', 'recipient'])
            ->forCompany($companyId->toInt())
            ->forSender($senderId->toInt())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByRecipient(CompanyId $companyId, UserId $recipientId): array
    {
        return SaMessageModel::with(['sender', 'recipient'])
            ->forCompany($companyId->toInt())
            ->forRecipient($recipientId->toInt())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findConversation(CompanyId $companyId, UserId $user1, UserId $user2): array
    {
        return SaMessageModel::with(['sender', 'recipient'])
            ->forCompany($companyId->toInt())
            ->where(function ($q) use ($user1, $user2) {
                $q->where(function ($q) use ($user1, $user2) {
                    $q->where('sender_id', $user1->toInt())->where('recipient_id', $user2->toInt());
                })->orWhere(function ($q) use ($user1, $user2) {
                    $q->where('sender_id', $user2->toInt())->where('recipient_id', $user1->toInt());
                });
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findReplies(MessageId $parentId): array
    {
        return SaMessageModel::with(['sender', 'recipient'])
            ->where('parent_id', $parentId->toInt())
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function countUnreadByRecipient(CompanyId $companyId, UserId $recipientId): int
    {
        return SaMessageModel::forCompany($companyId->toInt())
            ->forRecipient($recipientId->toInt())
            ->unread()
            ->count();
    }

    public function save(Message $message): Message
    {
        $data = [
            'sa_company_id' => $message->getCompanyId()->toInt(),
            'sender_id' => $message->getSenderIdValue(),
            'recipient_id' => $message->getRecipientIdValue(),
            'subject' => $message->getSubject(),
            'body' => $message->getBody(),
            'is_read' => $message->isRead(),
            'read_at' => $message->getReadAt()?->format('Y-m-d H:i:s'),
            'parent_id' => $message->getParentIdValue(),
        ];

        if ($message->id() === 0) {
            $model = SaMessageModel::create($data);
        } else {
            $model = SaMessageModel::find($message->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function markAsRead(MessageId $id): void
    {
        SaMessageModel::where('id', $id->toInt())->update(['is_read' => true, 'read_at' => now()]);
    }

    public function markAllAsRead(CompanyId $companyId, UserId $recipientId): void
    {
        SaMessageModel::forCompany($companyId->toInt())
            ->forRecipient($recipientId->toInt())
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    private function toDomainEntity(SaMessageModel $model): Message
    {
        $parentId = $model->parent_id ? MessageId::fromInt($model->parent_id) : null;

        return Message::reconstitute(
            id: MessageId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            senderId: UserId::fromInt($model->sender_id),
            recipientId: UserId::fromInt($model->recipient_id),
            subject: $model->subject,
            body: $model->body,
            isRead: (bool) $model->is_read,
            readAt: $model->read_at ? new DateTimeImmutable($model->read_at->format('Y-m-d H:i:s')) : null,
            parentId: $parentId,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
