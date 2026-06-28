<?php

namespace App\Domain\LoyaltyReward\Entities;

class LgrQualification
{
    public const REQUIRED_FIRST_LEVEL_MEMBERS = 3;
    public const REQUIRED_ACTIVITIES = 2;

    private function __construct(
        private ?int $id,
        private int $userId,
        private bool $starterPackageCompleted,
        private bool $trainingCompleted,
        private int $firstLevelMembers,
        private bool $networkRequirementMet,
        private int $activitiesCompleted,
        private bool $activityRequirementMet,
        private bool $fullyQualified
    ) {}

    public static function create(int $userId): self
    {
        return new self(
            id: null,
            userId: $userId,
            starterPackageCompleted: false,
            trainingCompleted: false,
            firstLevelMembers: 0,
            networkRequirementMet: false,
            activitiesCompleted: 0,
            activityRequirementMet: false,
            fullyQualified: false
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            userId: $data['user_id'],
            starterPackageCompleted: $data['starter_package_completed'],
            trainingCompleted: $data['training_completed'],
            firstLevelMembers: $data['first_level_members'],
            networkRequirementMet: $data['network_requirement_met'],
            activitiesCompleted: $data['activities_completed'],
            activityRequirementMet: $data['activity_requirement_met'],
            fullyQualified: $data['fully_qualified']
        );
    }

    public function completeStarterPackage(): void
    {
        $this->starterPackageCompleted = true;
        $this->checkFullQualification();
    }

    public function completeTraining(): void
    {
        $this->trainingCompleted = true;
        $this->checkFullQualification();
    }

    public function updateFirstLevelMembers(int $count): void
    {
        $this->firstLevelMembers = $count;
        $this->networkRequirementMet = $count >= self::REQUIRED_FIRST_LEVEL_MEMBERS;
        $this->checkFullQualification();
    }

    public function incrementActivity(): void
    {
        $this->activitiesCompleted++;
        $this->activityRequirementMet = $this->activitiesCompleted >= self::REQUIRED_ACTIVITIES;
        $this->checkFullQualification();
    }

    private function checkFullQualification(): void
    {
        $this->fullyQualified = $this->starterPackageCompleted
            && $this->trainingCompleted
            && $this->networkRequirementMet
            && $this->activityRequirementMet;
    }

    public function isFullyQualified(): bool
    {
        return $this->fullyQualified;
    }

    public function getQualificationProgress(): array
    {
        return [
            'starter_package' => [
                'completed' => $this->starterPackageCompleted,
                'required' => true,
            ],
            'training' => [
                'completed' => $this->trainingCompleted,
                'required' => true,
            ],
            'network' => [
                'current' => $this->firstLevelMembers,
                'required' => self::REQUIRED_FIRST_LEVEL_MEMBERS,
                'met' => $this->networkRequirementMet,
            ],
            'activities' => [
                'current' => $this->activitiesCompleted,
                'required' => self::REQUIRED_ACTIVITIES,
                'met' => $this->activityRequirementMet,
            ],
            'fully_qualified' => $this->fullyQualified,
        ];
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function isStarterPackageCompleted(): bool { return $this->starterPackageCompleted; }
    public function isTrainingCompleted(): bool { return $this->trainingCompleted; }
    public function getFirstLevelMembers(): int { return $this->firstLevelMembers; }
    public function isNetworkRequirementMet(): bool { return $this->networkRequirementMet; }
    public function getActivitiesCompleted(): int { return $this->activitiesCompleted; }
    public function isActivityRequirementMet(): bool { return $this->activityRequirementMet; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'starter_package_completed' => $this->starterPackageCompleted,
            'training_completed' => $this->trainingCompleted,
            'first_level_members' => $this->firstLevelMembers,
            'network_requirement_met' => $this->networkRequirementMet,
            'activities_completed' => $this->activitiesCompleted,
            'activity_requirement_met' => $this->activityRequirementMet,
            'fully_qualified' => $this->fullyQualified,
            'progress' => $this->getQualificationProgress(),
        ];
    }
}
