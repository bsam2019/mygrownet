<?php

namespace App\Domain\Library\Entities;

use App\Domain\Library\ValueObjects\ResourceType;
use App\Domain\Library\ValueObjects\ResourceCategory;
use InvalidArgumentException;

class LibraryResource
{
    private function __construct(
        private readonly int $id,
        private string $title,
        private ?string $description,
        private ResourceType $type,
        private ResourceCategory $category,
        private string $resourceUrl,
        private ?string $author,
        private ?int $durationMinutes,
        private string $difficulty,
        private bool $isExternal,
        private bool $isFeatured,
        private bool $isActive,
        private int $viewCount
    ) {
        $this->validateTitle($title);
        $this->validateResourceUrl($resourceUrl);
        $this->validateDifficulty($difficulty);
    }

    public static function create(
        int $id,
        string $title,
        ?string $description,
        ResourceType $type,
        ResourceCategory $category,
        string $resourceUrl,
        ?string $author = null,
        ?int $durationMinutes = null,
        string $difficulty = 'beginner',
        bool $isExternal = true,
        bool $isFeatured = false,
        bool $isActive = true
    ): self {
        return new self(
            $id,
            $title,
            $description,
            $type,
            $category,
            $resourceUrl,
            $author,
            $durationMinutes,
            $difficulty,
            $isExternal,
            $isFeatured,
            $isActive,
            0 // Initial view count
        );
    }

    private function validateTitle(string $title): void
    {
        if (empty(trim($title))) {
            throw new InvalidArgumentException('Title cannot be empty');
        }

        if (strlen($title) > 255) {
            throw new InvalidArgumentException('Title cannot exceed 255 characters');
        }
    }

    private function validateResourceUrl(string $url): void
    {
        if (empty(trim($url))) {
            throw new InvalidArgumentException('Resource URL cannot be empty');
        }
    }

    private function validateDifficulty(string $difficulty): void
    {
        if (!in_array($difficulty, ['beginner', 'intermediate', 'advanced'])) {
            throw new InvalidArgumentException('Invalid difficulty level');
        }
    }

    // Business Logic Methods

    public function incrementViewCount(): void
    {
        $this->viewCount++;
    }

    public function markAsFeatured(): void
    {
        $this->isFeatured = true;
    }

    public function unmarkAsFeatured(): void
    {
        $this->isFeatured = false;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function updateDetails(
        string $title,
        ?string $description,
        ResourceType $type,
        ResourceCategory $category,
        string $resourceUrl,
        ?string $author,
        ?int $durationMinutes,
        string $difficulty
    ): void {
        $this->validateTitle($title);
        $this->validateResourceUrl($resourceUrl);
        $this->validateDifficulty($difficulty);

        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->category = $category;
        $this->resourceUrl = $resourceUrl;
        $this->author = $author;
        $this->durationMinutes = $durationMinutes;
        $this->difficulty = $difficulty;
    }

    public function isAccessibleToMember(): bool
    {
        return $this->isActive;
    }

    public function getDurationFormatted(): ?string
    {
        if (!$this->durationMinutes) {
            return null;
        }

        if ($this->durationMinutes < 60) {
            return $this->durationMinutes . ' min';
        }

        $hours = floor($this->durationMinutes / 60);
        $minutes = $this->durationMinutes % 60;
        
        return $minutes > 0 ? "{$hours}h {$minutes}m" : "{$hours}h";
    }

    // Getters

    public function id(): int
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function type(): ResourceType
    {
        return $this->type;
    }

    public function category(): ResourceCategory
    {
        return $this->category;
    }

    public function resourceUrl(): string
    {
        return $this->resourceUrl;
    }

    public function author(): ?string
    {
        return $this->author;
    }

    public function durationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function difficulty(): string
    {
        return $this->difficulty;
    }

    public function isExternal(): bool
    {
        return $this->isExternal;
    }

    public function isFeatured(): bool
    {
        return $this->isFeatured;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function viewCount(): int
    {
        return $this->viewCount;
    }
}
