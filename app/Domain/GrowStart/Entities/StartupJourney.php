<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\Entities;

use App\Domain\GrowStart\ValueObjects\JourneyId;
use App\Domain\GrowStart\ValueObjects\JourneyStatus;
use App\Domain\GrowStart\ValueObjects\JourneyProgress;
use DateTimeImmutable;

/**
 * GrowStart Startup Journey Entity
 * 
 * Represents a user's startup journey through the 8 stages.
 */
class StartupJourney
{
    private function __construct(
        private JourneyId $id,
        private int $userId,
        private int $industryId,
        private int $countryId,
        private string $businessName,
        private ?string $businessDescription,
        private int $currentStageId,
        private DateTimeImmutable $startedAt,
        private ?DateTimeImmutable $targetLaunchDate,
        private JourneyStatus $status,
        private bool $isPremium,
        private ?string $province,
        private ?string $city,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $userId,
        int $industryId,
        int $countryId,
        string $businessName,
        int $initialStageId,
        ?string $businessDescription = null,
        ?DateTimeImmutable $targetLaunchDate = null,
        ?string $province = null,
        ?string $city = null
    ): self {
        $now = new DateTimeImmutable();
        
        return new self(
            id: JourneyId::generate(),
            userId: $userId,
            industryId: $industryId,
            countryId: $countryId,
            businessName: $businessName,
            businessDescription: $businessDescription,
            currentStageId: $initialStageId,
            startedAt: $now,
            targetLaunchDate: $targetLaunchDate,
            status: JourneyStatus::active(),
            isPremium: false,
            province: $province,
            city: $city,
            createdAt: $now,
            updatedAt: $now
        );
    }

    public static function reconstitute(
        JourneyId $id,
        int $userId,
        int $industryId,
        int $countryId,
        string $businessName,
        ?string $businessDescription,
        int $currentStageId,
        DateTimeImmutable $startedAt,
        ?DateTimeImmutable $targetLaunchDate,
        JourneyStatus $status,
        bool $isPremium,
        ?string $province,
        ?string $city,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            $id, $userId, $industryId, $countryId, $businessName, $businessDescription,
            $currentStageId, $startedAt, $targetLaunchDate, $status, $isPremium,
            $province, $city, $createdAt, $updatedAt
        );
    }

    public function updateBusinessInfo(
        string $businessName,
        ?string $businessDescription = null,
        ?string $province = null,
        ?string $city = null
    ): void {
        $this->businessName = $businessName;
        $this->businessDescription = $businessDescription;
        $this->province = $province;
        $this->city = $city;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setTargetLaunchDate(?DateTimeImmutable $date): void
    {
        $this->targetLaunchDate = $date;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function advanceToStage(int $stageId): void
    {
        $this->currentStageId = $stageId;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function pause(): void
    {
        if (!$this->status->canTransitionTo(JourneyStatus::paused())) {
            throw new \DomainException('Cannot pause journey in current status');
        }
        $this->status = JourneyStatus::paused();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function resume(): void
    {
        if (!$this->status->canTransitionTo(JourneyStatus::active())) {
            throw new \DomainException('Cannot resume journey in current status');
        }
        $this->status = JourneyStatus::active();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function complete(): void
    {
        if (!$this->status->canTransitionTo(JourneyStatus::completed())) {
            throw new \DomainException('Cannot complete journey in current status');
        }
        $this->status = JourneyStatus::completed();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function archive(): void
    {
        if (!$this->status->canTransitionTo(JourneyStatus::archived())) {
            throw new \DomainException('Cannot archive journey in current status');
        }
        $this->status = JourneyStatus::archived();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function upgradeToPremium(): void
    {
        $this->isPremium = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getDaysActive(): int
    {
        $now = new DateTimeImmutable();
        return $this->startedAt->diff($now)->days;
    }

    public function isOnTrack(): bool
    {
        if (!$this->targetLaunchDate) {
            return true;
        }
        return $this->targetLaunchDate >= new DateTimeImmutable();
    }

    // Getters
    public function getId(): JourneyId { return $this->id; }
    public function id(): int { return $this->id->toInt(); }
    public function getUserId(): int { return $this->userId; }
    public function getIndustryId(): int { return $this->industryId; }
    public function getCountryId(): int { return $this->countryId; }
    public function getBusinessName(): string { return $this->businessName; }
    public function getBusinessDescription(): ?string { return $this->businessDescription; }
    public function getCurrentStageId(): int { return $this->currentStageId; }
    public function getStartedAt(): DateTimeImmutable { return $this->startedAt; }
    public function getTargetLaunchDate(): ?DateTimeImmutable { return $this->targetLaunchDate; }
    public function getStatus(): JourneyStatus { return $this->status; }
    public function isPremium(): bool { return $this->isPremium; }
    public function getProvince(): ?string { return $this->province; }
    public function getCity(): ?string { return $this->city; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'user_id' => $this->userId,
            'industry_id' => $this->industryId,
            'country_id' => $this->countryId,
            'business_name' => $this->businessName,
            'business_description' => $this->businessDescription,
            'current_stage_id' => $this->currentStageId,
            'started_at' => $this->startedAt->format('Y-m-d H:i:s'),
            'target_launch_date' => $this->targetLaunchDate?->format('Y-m-d'),
            'status' => $this->status->value(),
            'is_premium' => $this->isPremium,
            'province' => $this->province,
            'city' => $this->city,
            'days_active' => $this->getDaysActive(),
            'is_on_track' => $this->isOnTrack(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
