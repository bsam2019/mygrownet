<?php

namespace App\Domain\Storage\Entities;

class StorageFolder
{
    private function __construct(
        private string $id,
        private int $userId,
        private ?string $parentId,
        private string $name,
        private ?string $pathCache = null
    ) {}

    public static function create(
        string $id,
        int $userId,
        ?string $parentId,
        string $name
    ): self {
        if (empty($name)) {
            throw new \DomainException('Folder name cannot be empty');
        }

        return new self($id, $userId, $parentId, $name);
    }

    public function rename(string $newName): void
    {
        if (empty($newName)) {
            throw new \DomainException('Folder name cannot be empty');
        }

        $this->name = $newName;
    }

    public function moveTo(?string $newParentId): void
    {
        // Prevent moving folder into itself
        if ($newParentId === $this->id) {
            throw new \DomainException('Cannot move folder into itself');
        }

        $this->parentId = $newParentId;
    }

    public function updatePathCache(string $path): void
    {
        $this->pathCache = $path;
    }

    public function belongsToUser(int $userId): bool
    {
        return $this->userId === $userId;
    }

    public function isRoot(): bool
    {
        return $this->parentId === null;
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPathCache(): ?string
    {
        return $this->pathCache;
    }
}
