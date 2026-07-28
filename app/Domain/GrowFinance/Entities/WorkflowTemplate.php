<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use DateTimeImmutable;

class WorkflowTemplate
{
    public readonly ?int $id;
    public readonly int $businessId;
    public readonly string $name;
    public readonly ?string $description;
    public readonly string $entityType;
    public readonly array $steps;
    public readonly bool $isActive;
    public readonly ?int $slaHours;
    public readonly bool $allowEscalation;
    public readonly ?DateTimeImmutable $createdAt;
    public readonly ?DateTimeImmutable $updatedAt;

    public function __construct(
        ?int $id,
        int $businessId,
        string $name,
        ?string $description,
        string $entityType,
        array $steps = [],
        bool $isActive = true,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
        ?int $slaHours = null,
        bool $allowEscalation = false,
    ) {
        $this->id = $id;
        $this->businessId = $businessId;
        $this->name = $name;
        $this->description = $description;
        $this->entityType = $entityType;
        $this->steps = $steps;
        $this->isActive = $isActive;
        $this->slaHours = $slaHours;
        $this->allowEscalation = $allowEscalation;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            entityType: $data['entity_type'],
            steps: isset($data['steps']) ? (is_string($data['steps']) ? json_decode($data['steps'], true) : $data['steps']) : [],
            isActive: (bool) ($data['is_active'] ?? true),
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
            slaHours: isset($data['sla_hours']) ? (int) $data['sla_hours'] : null,
            allowEscalation: (bool) ($data['allow_escalation'] ?? false),
        );
    }

    public function addStep(int $order, string $role, ?int $approverId, string $action = 'approve'): void
    {
        $steps = $this->steps;
        $steps[] = [
            'step_order' => $order,
            'role' => $role,
            'approver_id' => $approverId,
            'action' => $action,
        ];
        usort($steps, fn(array $a, array $b) => ($a['step_order'] ?? 0) <=> ($b['step_order'] ?? 0));
        // Re-index
        $this->steps = array_values($steps);
    }

    public function removeStep(int $order): void
    {
        $this->steps = array_values(
            array_filter($this->steps, fn(array $step) => ($step['step_order'] ?? 0) !== $order)
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'name' => $this->name,
            'description' => $this->description,
            'entity_type' => $this->entityType,
            'steps' => json_encode($this->steps),
            'is_active' => $this->isActive,
            'sla_hours' => $this->slaHours,
            'allow_escalation' => $this->allowEscalation,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
