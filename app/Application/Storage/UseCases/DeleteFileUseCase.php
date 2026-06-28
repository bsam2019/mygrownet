<?php

namespace App\Application\Storage\UseCases;

use App\Domain\Storage\Services\QuotaEnforcementService;
use App\Infrastructure\Storage\S3\S3StorageService;
use App\Domain\Storage\Repositories\StorageFileRepositoryInterface;

class DeleteFileUseCase
{
    public function __construct(
        private QuotaEnforcementService $quotaService,
        private S3StorageService $s3Service,
        private StorageFileRepositoryInterface $fileRepository
    ) {}

    public function execute(string $fileId, int $userId): void
    {
        $file = $this->fileRepository->findById($fileId);

        if (!$file || $file->getUserId() !== $userId) {
            throw new \DomainException('File not found or access denied');
        }

        // Delete from S3
        $this->s3Service->deleteFile($file->getS3Path()->getKey());

        // Update usage
        $this->quotaService->decrementUsage($userId, $file->getSize());

        // Delete from repository
        $this->fileRepository->delete($file);

        // Dispatch domain event
        event(new \App\Domain\Storage\Events\FileDeleted(
            $file->getId(),
            $userId,
            $file->getSize()->toBytes(),
            new \DateTimeImmutable()
        ));
    }
}
