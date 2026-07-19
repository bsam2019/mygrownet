<?php

namespace App\Domain\Workspace\ValueObjects;

use App\Domain\Core\Models\Organization;

class WorkspaceContext
{
    public string $type = 'guest';
    public ?int $organizationId = null;
    public ?string $organizationSlug = null;
    public ?string $organizationName = null;
    public ?int $applicationId = null;
    public ?Organization $organization = null;

    public function setPersonal(): self
    {
        $this->type = 'personal';
        $this->organizationId = null;
        $this->organizationSlug = null;
        $this->organizationName = null;
        return $this;
    }

    public function setOrganization(Organization $org): self
    {
        $this->type = 'organization';
        $this->organization = $org;
        $this->organizationId = $org->id;
        $this->organizationSlug = $org->slug;
        $this->organizationName = $org->name;
        return $this;
    }

    public function setGuest(): self
    {
        $this->type = 'guest';
        return $this;
    }

    public function withApplication(int $applicationId): self
    {
        $this->applicationId = $applicationId;
        return $this;
    }

    public function isPersonal(): bool
    {
        return $this->type === 'personal';
    }

    public function isOrganization(): bool
    {
        return $this->type === 'organization';
    }

    public function isGuest(): bool
    {
        return $this->type === 'guest';
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'organization_id' => $this->organizationId,
            'organization_slug' => $this->organizationSlug,
            'organization_name' => $this->organizationName,
            'application_id' => $this->applicationId,
        ];
    }

    public static function fromArray(array $data): self
    {
        $context = new self();
        $context->type = $data['type'] ?? 'guest';
        $context->organizationId = $data['organization_id'] ?? null;
        $context->organizationSlug = $data['organization_slug'] ?? null;
        $context->organizationName = $data['organization_name'] ?? null;
        $context->applicationId = $data['application_id'] ?? null;
        return $context;
    }
}
