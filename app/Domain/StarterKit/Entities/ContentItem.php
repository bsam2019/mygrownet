<?php

namespace App\Domain\StarterKit\Entities;

use App\Domain\StarterKit\ValueObjects\Money;
use InvalidArgumentException;

class ContentItem
{
    private function __construct(
        private readonly int $id,
        private string $title,
        private ?string $description,
        private string $category,
        private int $unlockDay,
        private ?string $filePath,
        private ?string $fileType,
        private ?int $fileSize,
        private ?string $thumbnail,
        private Money $estimatedValue,
        private int $sortOrder,
        private bool $isActive
    ) {
        $this->validateTitle($title);
        $this->validateUnlockDay($unlockDay);
    }

    public static function create(
        int $id,
        string $title,
        ?string $description,
        string $category,
        int $unlockDay,
        Money $estimatedValue,
        ?string $filePath = null,
        ?string $fileType = null,
        ?int $fileSize = null,
        ?string $thumbnail = null,
        int $sortOrder = 0,
        bool $isActive = true
    ): self {
        return new self(
            $id,
            $title,
            $description,
            $category,
            $unlockDay,
            $filePath,
            $fileType,
            $fileSize,
            $thumbnail,
            $estimatedValue,
            $sortOrder,
            $isActive
        );
    }

    private function validateTitle(string $title): void
    {
        if (empty(trim($title))) {
            throw new InvalidArgumentException('Title cannot be empty');
        }
    }

    private function validateUnlockDay(int $unlockDay): void
    {
        if ($unlockDay < 0 || $unlockDay > 30) {
            throw new InvalidArgumentException('Unlock day must be between 0 and 30');
        }
    }

    // Business Logic

    public function isImmediateAccess(): bool
    {
        return $this->unlockDay === 0;
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
        string $category,
        int $unlockDay,
        Money $estimatedValue
    ): void {
        $this->validateTitle($title);
        $this->validateUnlockDay($unlockDay);

        $this->title = $title;
        $this->description = $description;
        $this->category = $category;
        $this->unlockDay = $unlockDay;
        $this->estimatedValue = $estimatedValue;
    }

    public function updateFile(?string $filePath, ?string $fileType, ?int $fileSize): void
    {
        $this->filePath = $filePath;
        $this->fileType = $fileType;
        $this->fileSize = $fileSize;
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

    public function category(): string
    {
        return $this->category;
    }

    public function unlockDay(): int
    {
        return $this->unlockDay;
    }

    public function filePath(): ?string
    {
        return $this->filePath;
    }

    public function fileType(): ?string
    {
        return $this->fileType;
    }

    public function fileSize(): ?int
    {
        return $this->fileSize;
    }

    public function thumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function estimatedValue(): Money
    {
        return $this->estimatedValue;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
