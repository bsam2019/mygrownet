<?php

namespace App\Domain\PrimeEdge\Entities;

use App\Domain\PrimeEdge\ValueObjects\EngagementId;
use App\Domain\PrimeEdge\ValueObjects\EngagementType;
use App\Domain\PrimeEdge\ValueObjects\EngagementStatus;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\Money;
use DateTimeImmutable;

class Engagement
{
    private function __construct(
        private readonly EngagementId $id,
        private readonly ClientId $clientId,
        private EngagementType $type,
        private string $description,
        private ?string $scope,
        private EngagementStatus $status,
        private ?Money $agreedFee,
        private ?string $notes,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $startedAt,
        private ?DateTimeImmutable $completedAt,
        private ?DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        EngagementId $id,
        ClientId $clientId,
        EngagementType $type,
        string $description,
        ?string $scope = null,
        ?Money $agreedFee = null,
        ?string $notes = null,
    ): self {
        return new self(
            id: $id,
            clientId: $clientId,
            type: $type,
            description: $description,
            scope: $scope,
            status: EngagementStatus::PENDING,
            agreedFee: $agreedFee,
            notes: $notes,
            createdAt: new DateTimeImmutable(),
            startedAt: null,
            completedAt: null,
            updatedAt: null,
        );
    }

    public static function reconstitute(
        EngagementId $id,
        ClientId $clientId,
        EngagementType $type,
        string $description,
        ?string $scope,
        EngagementStatus $status,
        ?Money $agreedFee,
        ?string $notes,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $startedAt,
        ?DateTimeImmutable $completedAt,
        ?DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            clientId: $clientId,
            type: $type,
            description: $description,
            scope: $scope,
            status: $status,
            agreedFee: $agreedFee,
            notes: $notes,
            createdAt: $createdAt,
            startedAt: $startedAt,
            completedAt: $completedAt,
            updatedAt: $updatedAt,
        );
    }

    public function start(): void
    {
        if (!$this->status->canTransitionTo(EngagementStatus::IN_PROGRESS)) {
            throw new \DomainException("Cannot start engagement in status {$this->status->value}");
        }
        $this->status = EngagementStatus::IN_PROGRESS;
        $this->startedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function complete(): void
    {
        if (!$this->status->canTransitionTo(EngagementStatus::COMPLETED)) {
            throw new \DomainException("Cannot complete engagement in status {$this->status->value}");
        }
        $this->status = EngagementStatus::COMPLETED;
        $this->completedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function cancel(?string $reason = null): void
    {
        if (!$this->status->canTransitionTo(EngagementStatus::CANCELLED)) {
            throw new \DomainException("Cannot cancel engagement in status {$this->status->value}");
        }
        $this->status = EngagementStatus::CANCELLED;
        $this->notes = $reason;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateScope(string $scope): void
    {
        if ($this->status->isTerminal()) {
            throw new \DomainException('Cannot update scope of a completed or cancelled engagement');
        }
        $this->scope = $scope;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateFee(Money $fee): void
    {
        if ($this->status === EngagementStatus::COMPLETED) {
            throw new \DomainException('Cannot update fee of a completed engagement');
        }
        $this->agreedFee = $fee;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): EngagementId { return $this->id; }
    public function clientId(): ClientId { return $this->clientId; }
    public function type(): EngagementType { return $this->type; }
    public function description(): string { return $this->description; }
    public function scope(): ?string { return $this->scope; }
    public function status(): EngagementStatus { return $this->status; }
    public function agreedFee(): ?Money { return $this->agreedFee; }
    public function notes(): ?string { return $this->notes; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function startedAt(): ?DateTimeImmutable { return $this->startedAt; }
    public function completedAt(): ?DateTimeImmutable { return $this->completedAt; }
    public function updatedAt(): ?DateTimeImmutable { return $this->updatedAt; }
}
