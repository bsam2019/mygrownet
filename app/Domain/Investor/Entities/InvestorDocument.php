<?php

namespace App\Domain\Investor\Entities;

use App\Domain\Investor\ValueObjects\DocumentCategory;
use App\Domain\Investor\ValueObjects\DocumentStatus;
use DateTimeImmutable;

class InvestorDocument
{
    public function __construct(
        private int $id,
        private string $title,
        private string $description,
        private DocumentCategory $category,
        private string $filePath,
        private string $fileName,
        private int $fileSize,
        private string $mimeType,
        private DateTimeImmutable $uploadDate,
        private int $uploadedBy,
        private bool $visibleToAll,
        private ?int $investmentRoundId,
        private DocumentStatus $status,
        private int $downloadCount = 0
    ) {}

    // Getters
    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getCategory(): DocumentCategory { return $this->category; }
    public function getFilePath(): string { return $this->filePath; }
    public function getFileName(): string { return $this->fileName; }
    public function getFileSize(): int { return $this->fileSize; }
    public function getMimeType(): string { return $this->mimeType; }
    public function getUploadDate(): DateTimeImmutable { return $this->uploadDate; }
    public function getUploadedBy(): int { return $this->uploadedBy; }
    public function isVisibleToAll(): bool { return $this->visibleToAll; }
    public function getInvestmentRoundId(): ?int { return $this->investmentRoundId; }
    public function getStatus(): DocumentStatus { return $this->status; }
    public function getDownloadCount(): int { return $this->downloadCount; }

    // Business methods
    public function incrementDownloadCount(): void
    {
        $this->downloadCount++;
    }

    public function isAccessibleByInvestor(int $investorRoundId): bool
    {
        if (!$this->status->isActive()) {
            return false;
        }

        if ($this->visibleToAll) {
            return true;
        }

        return $this->investmentRoundId === $investorRoundId;
    }

    public function getFormattedFileSize(): string
    {
        $bytes = $this->fileSize;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileExtension(): string
    {
        return strtoupper(pathinfo($this->fileName, PATHINFO_EXTENSION));
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mimeType, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mimeType === 'application/pdf';
    }

    public function isDocument(): bool
    {
        return in_array($this->mimeType, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function canBePreviewedInBrowser(): bool
    {
        return $this->isPdf() || $this->isImage();
    }

    // Setters for updates
    public function updateTitle(string $title): void
    {
        $this->title = $title;
    }

    public function updateDescription(string $description): void
    {
        $this->description = $description;
    }

    public function updateVisibility(bool $visibleToAll, ?int $investmentRoundId = null): void
    {
        $this->visibleToAll = $visibleToAll;
        $this->investmentRoundId = $investmentRoundId;
    }

    public function archive(): void
    {
        $this->status = DocumentStatus::ARCHIVED;
    }

    public function activate(): void
    {
        $this->status = DocumentStatus::ACTIVE;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category->value(),
            'category_label' => $this->category->label(),
            'category_icon' => $this->category->icon(),
            'file_path' => $this->filePath,
            'file_name' => $this->fileName,
            'file_size' => $this->fileSize,
            'formatted_file_size' => $this->getFormattedFileSize(),
            'file_extension' => $this->getFileExtension(),
            'mime_type' => $this->mimeType,
            'upload_date' => $this->uploadDate->format('Y-m-d H:i:s'),
            'upload_date_formatted' => $this->uploadDate->format('F j, Y'),
            'uploaded_by' => $this->uploadedBy,
            'visible_to_all' => $this->visibleToAll,
            'investment_round_id' => $this->investmentRoundId,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'download_count' => $this->downloadCount,
            'can_preview' => $this->canBePreviewedInBrowser(),
            'is_pdf' => $this->isPdf(),
            'is_image' => $this->isImage(),
            'is_document' => $this->isDocument(),
        ];
    }
}