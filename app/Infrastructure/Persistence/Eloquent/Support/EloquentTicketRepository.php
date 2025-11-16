<?php

namespace App\Infrastructure\Persistence\Eloquent\Support;

use App\Domain\Support\Entities\Ticket;
use App\Domain\Support\Repositories\TicketRepository;
use App\Domain\Support\ValueObjects\TicketId;
use App\Domain\Support\ValueObjects\UserId;
use App\Domain\Support\ValueObjects\TicketStatus;
use App\Domain\Support\ValueObjects\TicketCategory;
use App\Domain\Support\ValueObjects\TicketPriority;
use App\Domain\Support\ValueObjects\TicketContent;

class EloquentTicketRepository implements TicketRepository
{
    public function save(Ticket $ticket): void
    {
        SupportTicketModel::updateOrCreate(
            ['id' => $ticket->id()->value()],
            [
                'user_id' => $ticket->userId()->value(),
                'category' => $ticket->category()->value,
                'priority' => $ticket->priority()->value,
                'status' => $ticket->status()->value,
                'subject' => $ticket->subject(),
                'description' => $ticket->description()->value(),
                'assigned_to' => $ticket->assignedTo()?->value(),
                'resolved_at' => $ticket->resolvedAt(),
                'closed_at' => $ticket->closedAt(),
                'satisfaction_rating' => $ticket->satisfactionRating(),
            ]
        );
    }

    public function findById(TicketId $id): ?Ticket
    {
        $model = SupportTicketModel::find($id->value());
        
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByUserId(UserId $userId): array
    {
        return SupportTicketModel::where('user_id', $userId->value())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findByStatus(TicketStatus $status): array
    {
        return SupportTicketModel::where('status', $status->value)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findByAssignedTo(UserId $adminId): array
    {
        return SupportTicketModel::where('assigned_to', $adminId->value())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findAll(): array
    {
        return SupportTicketModel::orderBy('created_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findOverdue(int $hoursThreshold): array
    {
        $threshold = now()->subHours($hoursThreshold);
        
        return SupportTicketModel::whereNotIn('status', ['resolved', 'closed'])
            ->where('created_at', '<', $threshold)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function nextId(): TicketId
    {
        $maxId = SupportTicketModel::max('id') ?? 0;
        return TicketId::fromInt($maxId + 1);
    }

    private function toDomainEntity(SupportTicketModel $model): Ticket
    {
        $reflection = new \ReflectionClass(Ticket::class);
        $ticket = $reflection->newInstanceWithoutConstructor();

        $properties = [
            'id' => TicketId::fromInt($model->id),
            'userId' => UserId::fromInt($model->user_id),
            'category' => TicketCategory::fromString($model->category),
            'priority' => TicketPriority::fromString($model->priority),
            'status' => TicketStatus::fromString($model->status),
            'subject' => $model->subject,
            'description' => TicketContent::fromString($model->description),
            'assignedTo' => $model->assigned_to ? UserId::fromInt($model->assigned_to) : null,
            'createdAt' => new \DateTimeImmutable($model->created_at->toDateTimeString()),
            'resolvedAt' => $model->resolved_at ? new \DateTimeImmutable($model->resolved_at->toDateTimeString()) : null,
            'closedAt' => $model->closed_at ? new \DateTimeImmutable($model->closed_at->toDateTimeString()) : null,
            'satisfactionRating' => $model->satisfaction_rating,
        ];

        foreach ($properties as $name => $value) {
            $property = $reflection->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($ticket, $value);
        }

        return $ticket;
    }
}
