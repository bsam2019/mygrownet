<?php

namespace App\Domain\Tools\Entities;

use App\Domain\Tools\ValueObjects\BusinessPlanId;
use App\Domain\Tools\ValueObjects\MonetaryAmount;
use DateTimeImmutable;

class BusinessPlan
{
    private function __construct(
        private ?BusinessPlanId $id,
        private readonly int $userId,
        private string $businessName,
        private string $vision,
        private string $targetMarket,
        private MonetaryAmount $incomeGoal6Months,
        private MonetaryAmount $incomeGoal1Year,
        private int $teamSizeGoal,
        private string $marketingStrategy,
        private string $actionPlan,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $userId,
        string $businessName,
        string $vision,
        string $targetMarket,
        MonetaryAmount $incomeGoal6Months,
        MonetaryAmount $incomeGoal1Year,
        int $teamSizeGoal,
        string $marketingStrategy,
        string $actionPlan
    ): self {
        $now = new DateTimeImmutable();
        
        return new self(
            id: null,
            userId: $userId,
            businessName: $businessName,
            vision: $vision,
            targetMarket: $targetMarket,
            incomeGoal6Months: $incomeGoal6Months,
            incomeGoal1Year: $incomeGoal1Year,
            teamSizeGoal: $teamSizeGoal,
            marketingStrategy: $marketingStrategy,
            actionPlan: $actionPlan,
            createdAt: $now,
            updatedAt: $now
        );
    }

    public static function reconstitute(
        BusinessPlanId $id,
        int $userId,
        string $businessName,
        string $vision,
        string $targetMarket,
        MonetaryAmount $incomeGoal6Months,
        MonetaryAmount $incomeGoal1Year,
        int $teamSizeGoal,
        string $marketingStrategy,
        string $actionPlan,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            $id,
            $userId,
            $businessName,
            $vision,
            $targetMarket,
            $incomeGoal6Months,
            $incomeGoal1Year,
            $teamSizeGoal,
            $marketingStrategy,
            $actionPlan,
            $createdAt,
            $updatedAt
        );
    }

    public function update(
        string $businessName,
        string $vision,
        string $targetMarket,
        MonetaryAmount $incomeGoal6Months,
        MonetaryAmount $incomeGoal1Year,
        int $teamSizeGoal,
        string $marketingStrategy,
        string $actionPlan
    ): void {
        $this->businessName = $businessName;
        $this->vision = $vision;
        $this->targetMarket = $targetMarket;
        $this->incomeGoal6Months = $incomeGoal6Months;
        $this->incomeGoal1Year = $incomeGoal1Year;
        $this->teamSizeGoal = $teamSizeGoal;
        $this->marketingStrategy = $marketingStrategy;
        $this->actionPlan = $actionPlan;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setId(BusinessPlanId $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('Cannot change existing ID');
        }
        $this->id = $id;
    }

    // Getters
    public function id(): ?BusinessPlanId
    {
        return $this->id;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function businessName(): string
    {
        return $this->businessName;
    }

    public function vision(): string
    {
        return $this->vision;
    }

    public function targetMarket(): string
    {
        return $this->targetMarket;
    }

    public function incomeGoal6Months(): MonetaryAmount
    {
        return $this->incomeGoal6Months;
    }

    public function incomeGoal1Year(): MonetaryAmount
    {
        return $this->incomeGoal1Year;
    }

    public function teamSizeGoal(): int
    {
        return $this->teamSizeGoal;
    }

    public function marketingStrategy(): string
    {
        return $this->marketingStrategy;
    }

    public function actionPlan(): string
    {
        return $this->actionPlan;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id?->value(),
            'user_id' => $this->userId,
            'business_name' => $this->businessName,
            'vision' => $this->vision,
            'target_market' => $this->targetMarket,
            'income_goal_6months' => $this->incomeGoal6Months->value(),
            'income_goal_1year' => $this->incomeGoal1Year->value(),
            'team_size_goal' => $this->teamSizeGoal,
            'marketing_strategy' => $this->marketingStrategy,
            'action_plan' => $this->actionPlan,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
ket,
        IncomeGoal $incomeGoal6Months,
        IncomeGoal $incomeGoal1Year,
        int $teamSizeGoal,
        string $marketingStrategy,
        string $actionPlan
    ): self {
        $now = new DateTimeImmutable();
        
        return new self(
            null,
            $userId,
            $businessName,
            $vision,
            $targetMarket,
            $incomeGoal6Months,
            $incomeGoal1Year,
            $teamSizeGoal,
            $marketingStrategy,
            $actionPlan,
            $now,
            $now
        );
    }

    public static function reconstitute(
        BusinessPlanId $id,
        int $userId,
        string $businessName,
        string $vision,
        string $targetMarket,
        IncomeGoal $incomeGoal6Months,
        IncomeGoal $incomeGoal1Year,
        int $teamSizeGoal,
        string $marketingStrategy,
        string $actionPlan,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            $id,
            $userId,
            $businessName,
            $vision,
            $targetMarket,
            $incomeGoal6Months,
            $incomeGoal1Year,
            $teamSizeGoal,
            $marketingStrategy,
            $actionPlan,
            $createdAt,
            $updatedAt
        );
    }

    public function update(
        string $businessName,
        string $vision,
        string $targetMarket,
        IncomeGoal $incomeGoal6Months,
        IncomeGoal $incomeGoal1Year,
        int $teamSizeGoal,
        string $marketingStrategy,
        string $actionPlan
    ): void {
        $this->businessName = $businessName;
        $this->vision = $vision;
        $this->targetMarket = $targetMarket;
        $this->incomeGoal6Months = $incomeGoal6Months;
        $this->incomeGoal1Year = $incomeGoal1Year;
        $this->teamSizeGoal = $teamSizeGoal;
        $this->marketingStrategy = $marketingStrategy;
        $this->actionPlan = $actionPlan;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setId(BusinessPlanId $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('Business plan ID already set');
        }
        $this->id = $id;
    }

    // Getters
    public function id(): ?BusinessPlanId
    {
        return $this->id;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function businessName(): string
    {
        return $this->businessName;
    }

    public function vision(): string
    {
        return $this->vision;
    }

    public function targetMarket(): string
    {
        return $this->targetMarket;
    }

    public function incomeGoal6Months(): IncomeGoal
    {
        return $this->incomeGoal6Months;
    }

    public function incomeGoal1Year(): IncomeGoal
    {
        return $this->incomeGoal1Year;
    }

    public function teamSizeGoal(): int
    {
        return $this->teamSizeGoal;
    }

    public function marketingStrategy(): string
    {
        return $this->marketingStrategy;
    }

    public function actionPlan(): string
    {
        return $this->actionPlan;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id?->value(),
            'user_id' => $this->userId,
            'business_name' => $this->businessName,
            'vision' => $this->vision,
            'target_market' => $this->targetMarket,
            'income_goal_6months' => $this->incomeGoal6Months->amount(),
            'income_goal_1year' => $this->incomeGoal1Year->amount(),
            'team_size_goal' => $this->teamSizeGoal,
            'marketing_strategy' => $this->marketingStrategy,
            'action_plan' => $this->actionPlan,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
