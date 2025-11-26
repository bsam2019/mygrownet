<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\InvestorMessage;
use App\Domain\Investor\Repositories\InvestorMessageRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Investor\InvestorMessageModel;
use DateTimeImmutable;

class EloquentInvestorMessageRepository implements InvestorMessageRepositoryInterface
{
    public function save(InvestorMessage $message): InvestorMessage
    {
        $model = $message->getId() 
            ? InvestorMessageModel::find($message->getId())
            : new InvestorMessageModel();

        $model->fill([
            'investor_account_id' => $message->getInvestorAccountId(),
            'admin_id' => $message->getAdminId(),
            'subject' => $message->getSubject(),
            'content' => $message->getContent(),
            'direction' => $message->getDirection(),
            'status' => $message->isRead() ? 'read' : 'unread',
            'read_at' => $message->getReadAt()?->format('Y-m-d H:i:s'),
            'parent_id' => $message->getParentId(),
        ]);

        $model->save();

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?InvestorMessage
    {
        $model = InvestorMessageModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByInvestorAccountId(int $investorAccountId, int $limit = 50, int $offset = 0): array
    {
        $models = InvestorMessageModel::forInvestor($investorAccountId)
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findConversation(int $investorAccountId, int $limit = 50): array
    {
        $models = InvestorMessageModel::forInvestor($investorAccountId)
            ->orderBy('created_at', 'asc')
            ->take($limit)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function getUnreadCountForInvestor(int $investorAccountId): int
    {
        return InvestorMessageModel::forInvestor($investorAccountId)
            ->outbound() // Messages from admin to investor
            ->unread()
            ->count();
    }

    public function getUnreadCountForAdmin(): int
    {
        return InvestorMessageModel::inbound() // Messages from investors to admin
            ->unread()
            ->count();
    }

    public function markAsRead(int $messageId): void
    {
        InvestorMessageModel::where('id', $messageId)
            ->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
    }

    public function markAllAsReadForInvestor(int $investorAccountId): void
    {
        InvestorMessageModel::forInvestor($investorAccountId)
            ->outbound()
            ->unread()
            ->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
    }

    public function getAllForAdmin(int $limit = 50, int $offset = 0): array
    {
        $models = InvestorMessageModel::with(['investorAccount', 'admin'])
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function delete(int $id): void
    {
        InvestorMessageModel::destroy($id);
    }

    private function toDomainEntity(InvestorMessageModel $model): InvestorMessage
    {
        return InvestorMessage::reconstitute(
            id: $model->id,
            investorAccountId: $model->investor_account_id,
            adminId: $model->admin_id,
            subject: $model->subject,
            content: $model->content,
            direction: $model->direction,
            isRead: $model->status === 'read',
            readAt: $model->read_at ? new DateTimeImmutable($model->read_at->format('Y-m-d H:i:s')) : null,
            parentId: $model->parent_id,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s'))
        );
    }
}
