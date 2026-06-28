<?php

namespace App\Domain\EmailMarketing\Entities;

use App\Domain\EmailMarketing\ValueObjects\CampaignId;
use App\Domain\EmailMarketing\ValueObjects\CampaignType;
use App\Domain\EmailMarketing\ValueObjects\CampaignStatus;
use App\Domain\EmailMarketing\ValueObjects\TriggerType;
use DateTimeImmutable;

class EmailCampaign
{
    private function __construct(
        private CampaignId $id,
        private string $name,
        private CampaignType $type,
        private CampaignStatus $status,
        private TriggerType $triggerType,
        private ?array $triggerConfig,
        private ?DateTimeImmutable $startDate,
        private ?DateTimeImmutable $endDate,
        private ?int $createdBy,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        string $name,
        CampaignType $type,
        TriggerType $triggerType,
        ?array $triggerConfig = null,
        ?int $createdBy = null
    ): self {
        return new self(
            CampaignId::generate(),
            $name,
            $type,
            CampaignStatus::draft(),
            $triggerType,
            $triggerConfig,
            null,
            null,
            $createdBy,
            new DateTimeImmutable(),
            new DateTimeImmutable()
        );
    }

    public function activate(?DateTimeImmutable $startDate = null): void
    {
        if (!$this->canBeActivated()) {
            throw new \DomainException('Campaign cannot be activated without sequences');
        }

        $this->status = CampaignStatus::active();
        $this->startDate = $startDate ?? new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function pause(): void
    {
        if (!$this->status->isActive()) {
            throw new \DomainException('Only active campaigns can be paused');
        }

        $this->status = CampaignStatus::paused();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function resume(): void
    {
        if (!$this->status->isPaused()) {
            throw new \DomainException('Only paused campaigns can be resumed');
        }

        $this->status = CampaignStatus::active();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function complete(): void
    {
        $this->status = CampaignStatus::completed();
        $this->endDate = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function canBeActivated(): bool
    {
        return $this->status->isDraft();
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    // Getters
    public function id(): CampaignId { return $this->id; }
    public function name(): string { return $this->name; }
    public function type(): CampaignType { return $this->type; }
    public function status(): CampaignStatus { return $this->status; }
    public function triggerType(): TriggerType { return $this->triggerType; }
    public function triggerConfig(): ?array { return $this->triggerConfig; }
    public function startDate(): ?DateTimeImmutable { return $this->startDate; }
    public function endDate(): ?DateTimeImmutable { return $this->endDate; }
    public function createdBy(): ?int { return $this->createdBy; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }
}
